<?php


use abc\models\system\FormGroup;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class FormGroupTest extends TestCase
{
    public function testFormGroupValidation()
    {
        $form = new FormGroup();
        $errors = [];
        try {
            $data = [
                'form_id' => false,
                'sort_order' => false,
                'status' => false,
            ];
            $form->validate($data);
        } catch (ValidationException $e) {
            $errors = $form->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'form_id' => 1,
                'sort_order' => 1,
                'status' => 1,
            ];
            $form->validate($data);
        } catch (ValidationException $e) {
            $errors = $form->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
