<?php


use abc\models\system\FieldValue;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class FieldValueTest extends TestCase
{
    public function testFieldValueValidation()
    {
        $field = new FieldValue();
        $errors = [];
        try {
            $data = [
                'field_id'           => false,
                'language_id'                  => false,
            ];
            $field->validate($data);
        } catch (ValidationException $e) {
            $errors = $field->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'field_id'           => 1,
                'language_id'  => 1,
            ];
            $field->validate($data);
        } catch (ValidationException $e) {
            $errors = $field->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
