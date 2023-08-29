<?php


use abc\models\system\StoreDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class StoreDescriptionTest extends TestCase
{
    public function testStoreDescriptionValidation()
    {
        $store = new StoreDescription();
        $errors = [];
        try {
            $data = [
                'store_id' => false,
                'language_id' => false,
            ];
            $store->validate($data);
        } catch (ValidationException $e) {
            $errors = $store->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'store_id' => 1,
                'language_id' => 1,
            ];
            $store->validate($data);
        } catch (ValidationException $e) {
            $errors = $store->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
