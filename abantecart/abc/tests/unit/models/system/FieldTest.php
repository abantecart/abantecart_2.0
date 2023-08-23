<?php


use abc\models\system\Field;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testFieldValidation()
    {
        $field = new Field();
        $errors = [];
        try {
            $data = [
                'form_id'           => false,
                'sort_order'                  => false,
                'status'             => false,
            ];
            $field->validate($data);
        } catch (ValidationException $e) {
            $errors = $field->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'form_id'           => 1,
                'sort_order'  => 1,
                'status' => 1,
            ];
            $field->validate($data);
        } catch (ValidationException $e) {
            $errors = $field->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
