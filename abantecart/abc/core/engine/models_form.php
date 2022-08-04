<?php

namespace abc\core\engine;

class ModelsForm
{
    /**
     * @var array
     */
    protected array $fields = [];
    /**
     * @var array
     *   [
     *    'abc\models\catalog\Product'  => ['weight', 'height'],
     *    'abc\models\catalog\Category' => [],
     *   ]
     */
    protected array $models = [];

    /**
     * @param array $models
     */
    public function __construct(array $models)
    {
        $this->models = $models;
        $this->fields = $this->getModelsFields();
    }

    /**
     * get not changet form fields array
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getModels(): array
    {
        return $this->models;
    }

    /**
     * @return array
     */
    private function getModelsFields(): array
    {
        $modelsFields = [];
        foreach ($this->models as $model => $fields) {
            $modelFields = (new $model())->getFormFields();
            foreach ($fields as $field) {
                if ($modelFields[$field]) {
                    $modelsFields[$model][$field] = $modelFields[$field];
                }
            }
        }
        return $modelsFields;
    }

    /**
     * @return bool|string
     * @throws \JsonException
     */
    public function toJson(): bool|string
    {
        return json_encode($this->fields, JSON_THROW_ON_ERROR);
    }
}
