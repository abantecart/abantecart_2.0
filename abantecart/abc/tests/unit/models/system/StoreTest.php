<?php


use abc\models\system\Store;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    public function testStoreValidation()
    {
        $store = new Store();
        $errors = [];
        try {
            $data = [
                'status' => false,
            ];
            $store->validate($data);
        } catch (ValidationException $e) {
            $errors = $store->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'status' => 1,
            ];
            $store->validate($data);
        } catch (ValidationException $e) {
            $errors = $store->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
