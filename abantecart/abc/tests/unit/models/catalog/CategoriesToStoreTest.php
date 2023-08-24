<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\CategoriesToStore;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class CategoriesToStoreTest extends TestCase
{
    public function testValidator()
    {
        $categoryToStore = new CategoriesToStore(
            [
                'category_id' => -1,
                'store_id' => -1,

            ]
        );
        $errors = [];
        try {
            $categoryToStore->validate();
        } catch (ValidationException $e) {
            $errors = $categoryToStore->errors()['validation'];
            //var_dump($errors);
        }

        $this->assertCount(2, $errors);


        $categoryToStore = new CategoriesToStore(
            [
                'category_id' => 1,
                'store_id' => 1,
            ]
        );
        $errors = [];
        try {
            $categoryToStore->validate();
        } catch (ValidationException $e) {
            $errors = $categoryToStore->errors()['validation'];
            //var_dump($errors);
        }
        $this->assertCount(0, $errors);

    }
}
