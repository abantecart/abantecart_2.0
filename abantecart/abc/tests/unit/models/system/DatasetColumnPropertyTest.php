<?php


use abc\models\system\DatasetColumnProperty;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class DatasetColumnPropertyTest extends TestCase
{

    public function testDefinitionValidation()
    {
        $dataset = new DatasetColumnProperty();
        $errors = [];
        try {
            $data = [
                'dataset_column_id'           => false,
            ];
            $dataset->validate($data);
        } catch (ValidationException $e) {
            $errors = $dataset->errors()['validation'];
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'dataset_column_id'           => 1,
            ];
            $dataset->validate($data);
        } catch (ValidationException $e) {
            $errors = $dataset->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
