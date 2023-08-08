<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\ProductOptionValue;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ProductOptionValueTest extends TestCase
{
    public function testValidator()
    {
        $product = new ProductOptionValue();
        $errors = [];
        try {
            $data = [
                'product_option_id'           => false,
                'product_id'                  => false,
                'group_id'             => false,
                'quantity'                  => false,
                'subtract'  => false,
                'attribute_value_id' => false,
                'default'        => false,
                'sort_order'            => false,
            ];
            $product->validate($data);
        } catch (ValidationException $e) {
            $errors = $product->errors()['validation'];
        }
        $this->assertCount(8, $errors);

        $errors = [];
        try {
            $data = [
                'product_option_id'           => 1,
                'product_id'                  => 2,
                'group_id'             => 2,
                'quantity'                  => 2,
                'subtract'  => 1,
                'attribute_value_id' => 1,
                'default'        => 2,
                'sort_order'            => 1,
            ];
            $product->validate($data);
        } catch (ValidationException $e) {
            $errors = $product->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
