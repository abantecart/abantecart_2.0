<?php


use abc\models\system\FieldsGroupDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class FieldsGroupDescriptionTest extends TestCase
{
    public function testFieldsGroupDescriptionValidation()
    {
        $fields = new FieldsGroupDescription();
        $errors = [];
        try {
            $data = [
                'group_id'           => false,
                'language_id'                  => false,
            ];
            $fields->validate($data);
        } catch (ValidationException $e) {
            $errors = $fields->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'group_id'           => 1,
                'language_id' => 1,
            ];
            $fields->validate($data);
        } catch (ValidationException $e) {
            $errors = $fields->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
