<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\ProductOption;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ProductOptionTest extends TestCase
{
    public function testValidator()
    {
        $product = new ProductOption();
        $errors = [];
        try {
            $data = [
                'attribute_id'  => false,
                'product_id'    => false,
                'group_id'      => false,
                'sort_order'    => false,
                'status'        => false,
                'required'      => false,
            ];
            $product->validate($data);
        } catch (ValidationException $e) {
            $errors = $product->errors()['validation'];
        }
        $this->assertCount(6, $errors);

        $errors = [];
        try {
            $data = [
                'attribute_id'  => 1,
                'product_id'    => 1,
                'group_id'      => 1,
                'sort_order'    => 1,
                'status'        => 1,
                'required'      => 1,
            ];
            $product->validate($data);
        } catch (ValidationException $e) {
            $errors = $product->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
