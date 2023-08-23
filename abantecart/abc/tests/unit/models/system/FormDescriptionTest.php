<?php


use abc\models\system\FormDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class FormDescriptionTest extends TestCase
{
    public function testFormDescriptionValidation()
    {
        $form = new FormDescription();
        $errors = [];
        try {
            $data = [
                'form_id'           => false,
                'language_id'                  => false,
            ];
            $form->validate($data);
        } catch (ValidationException $e) {
            $errors = $form->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'form_id'           => 1,
                'language_id'  => 1,
            ];
            $form->validate($data);
        } catch (ValidationException $e) {
            $errors = $form->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }}
