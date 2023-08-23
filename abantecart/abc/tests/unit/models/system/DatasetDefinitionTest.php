<?php


use abc\models\system\DatasetDefinition;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class DatasetDefinitionTest extends TestCase
{

    public function testDatasetValidate()
    {
        $dataset = new DatasetDefinition();
        $errors = [];
        try {
            $data = [
                'dataset_id'           => false,
                'dataset_column_sort_order'                  => false,
            ];
            $dataset->validate($data);
        } catch (ValidationException $e) {
            $errors = $dataset->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'dataset_id'           => 1,
                'dataset_column_sort_order'             => 36,
            ];
            $dataset->validate($data);
        } catch (ValidationException $e) {
            $errors = $dataset->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
