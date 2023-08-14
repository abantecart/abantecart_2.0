<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\ProductDiscount;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ProductDiscountTest extends TestCase
{
    public function testValidator()
    {
        $product = new ProductDiscount(
            [
                'product_id'        => false,
                'customer_group_id' => false,
                'quantity'          => false,
                'priority'          => false,

            ]
        );
        $errors = [];
        try {
            $product->validate();
        } catch (ValidationException $e) {
            $errors =  $product->errors()['validation'];
            //var_dump($errors);
        }

        $this->assertCount(4, $errors);


        $product = new ProductDiscount(
            [
                'product_id'        => 1,
                'customer_group_id' => 1,
                'quantity'          => 1,
                'priority'          => 1,

            ]
        );
        $errors = [];
        try {
            $product->validate();
        } catch (ValidationException $e) {
            $errors =  $product->errors()['validation'];
            //var_dump($errors);
        }
        $this->assertCount(0, $errors);

    }
}
