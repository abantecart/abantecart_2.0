<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\StockStatus;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class StockStatusTest extends TestCase
{
    public function testValidator()
    {
        $stock = new StockStatus();
        $errors = [];
        try {
            $data = [
                'stock_status_id' => false,
                'language_id' => false,
            ];
            $stock->validate($data);
        } catch (ValidationException $e) {
            $errors = $stock->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'stock_status_id' => 1,
                'language_id' => 1,
            ];
            $stock->validate($data);
        } catch (ValidationException $e) {
            $errors = $stock->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
