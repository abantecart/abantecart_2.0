<?php


use abc\models\system\DatasetValue;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class DatasetValueTest extends TestCase
{
    public function testDatasetValidation()
    {
        $dataset = new DatasetValue();
        $errors = [];
        try {
            $data = [
                'dataset_column_id'           => false,
                'value_integer'             => false,
                'row_id'                  => false,
            ];
            $dataset->validate($data);
        } catch (ValidationException $e) {
            $errors = $dataset->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'dataset_column_id'           => 1,
                'value_integer'             => 1,
                'row_id'  => 1,
            ];
            $dataset->validate($data);
        } catch (ValidationException $e) {
            $errors = $dataset->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
