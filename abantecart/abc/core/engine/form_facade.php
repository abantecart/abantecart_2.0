<?php

namespace abc\core\engine;

use abc\core\lib\AException;
use TheSeer\Tokenizer\Exception;

/**
 * @property AForm $formObj
 */
class FormFacade
{
    private $form;
    private $fields;
    private $modelsFields;
    public $formObj;

    /**
     * @throws AException
     */
    public static function withModels(array $form, array $models, array $staticFields = [], array $fieldsSettings = [])
    {
        $instance = new self();
        $instance->form = $form;
        $instance->modelsFields = (new ModelsForm($models))->getFields();
        $instance->mapModelsFieldsToForm();
        $instance->mapFieldsToForm($staticFields);
        $instance->applySettings($fieldsSettings);
        $instance->formObj = new AForm();
        $instance->formObj->setForm($form);
        $instance->formObj->setFields($instance->fields);
        return $instance;
    }

    /**
     * @return array
     * @throws AException
     * @throws \ReflectionException
     */
    public function getFormHtml(): array
    {
        $this->formObj->setForm(['form_name' => $this->form['name']]);
        return [
            'form_open' => $this->formObj->getFieldHtml($this->form),
            'fields'    => $this->getFieldsHtml(),
        ];

    }

    /**
     * @return string
     * @throws \JsonException
     */
    public function getFormJson(): string
    {
        return json_encode($this->fields, JSON_THROW_ON_ERROR);
    }

    public function getFieldsHtml(): array
    {
        $result = [];
        foreach ($this->fields as $fieldName => $field) {
            if ($field['group']) {
                if (!$result[$field['group']]) {
                    $result[$field['group']] = [];
                }
                $result[$field['group']][$fieldName] = $this->formObj->getFieldHtml($field);
            } else {
                $result[$fieldName] = $this->formObj->getFieldHtml($field);
            }
        }
        return $result;
    }

    /**
     * Apply fields settings
     *
     * @param array $fieldsSettings
     */
    private function applySettings(array $fieldsSettings): void
    {
        foreach ($fieldsSettings as $fieldName => $setting) {
            $this->fields[$fieldName] = array_merge($this->fields[$fieldName], $setting);
        }
        $this->sortFields();
    }

    /**
     * Sort fields by property 'sort_order' ASC
     */
    private function sortFields(): void
    {
        uasort($this->fields, function ($a, $b) {
            return ($a['sort_order'] <=> $b['sort_order']); //PHP 7+
        });
    }

    private function mapFieldsToForm(array $fields): void
    {
        foreach ($fields as $fieldName => $field) {
            if ($this->fields[$fieldName]) {
                throw new AException(
                    'Error: Duplicate field '.$$field.' in form !',
                );
            }
            $this->fields[$fieldName] = [
                'field_id'     => $fieldName,
                'form_id'      => $this->form['form_id'],
                'field_name'   => $fieldName,
                'name'         => $fieldName,
                'element_type' => $this->getCodeByElementType($field['type']),
                'type'         => $field['type'],
                'sort_order'   => $field['sort_order'],
                'required'     => $field['required'],
                'status'       => $field['status'],
                'description'  => $field['description'],
                'value'        => $field['value'],
                'group'        => $field['group'],
            ];
        }
    }

    /**
     * @param $type
     *
     * @return string
     * @throws AException
     */
    private function getCodeByElementType($type): string
    {
        $fieldTypeCode = HtmlElementFactory::getCodeByElementType($type);
        if (!$fieldTypeCode) {
            throw new AException(
                'Error: Could not load find element code for type '.$type.'!',
            );
        }
        return $fieldTypeCode;
    }

    /**
     * @throws AException
     */
    private
    function mapModelsFieldsToForm(): void
    {
        foreach ($this->modelsFields as $model => $fields) {
            $this->mapFieldsToForm($fields);
        }
    }

}
