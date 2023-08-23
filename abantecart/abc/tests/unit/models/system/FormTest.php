<?php


use abc\models\system\Form;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    public function testFormValidation()
    {
        $form = new Form();
        $errors = [];
        try {
            $data = [
                'status'           => false,

            ];
            $form->validate($data);
        } catch (ValidationException $e) {
            $errors = $form->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'status'           => 1,
            ];
            $form->validate($data);
        } catch (ValidationException $e) {
            $errors = $form->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
