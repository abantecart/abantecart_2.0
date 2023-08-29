<?php


use abc\models\system\FieldsGroup;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class FieldsGroupTest extends TestCase
{
    public function testValidator()
    {
        $field = new FieldsGroup();
        $errors = [];
        try {
            $data = [
                'field_id' => false,
                'group_id' => false,
                'sort_order' => false,
            ];
            $field->validate($data);
        } catch (ValidationException $e) {
            $errors = $field->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'field_id' => 1,
                'group_id' => 1,
                'sort_order' => 1,
            ];
            $field->validate($data);
        } catch (ValidationException $e) {
            $errors = $field->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
