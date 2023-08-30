<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * https://www.abantecart.com
 *
 * Copyright (c) 2011-2023  Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <https://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to https://www.abantecart.com for more information.
 */

namespace Tests\unit\models\catalog;

use abc\models\catalog\ProductOptionValue;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class ProductOptionValueTest extends ATestCase
{
    public function testValidator()
    {
        $product = new ProductOptionValue();
        $errors = [];
        try {
            $data = [
                'product_option_id' => false,
                'product_id' => false,
                'group_id' => false,
                'quantity' => false,
                'subtract' => false,
                'attribute_value_id' => false,
                'default' => false,
                'sort_order' => false,
            ];
            $product->validate($data);
        } catch (ValidationException $e) {
            $errors = $product->errors()['validation'];
        }
        $this->assertCount(8, $errors);

        $errors = [];
        try {
            $data = [
                'product_option_id' => 1,
                'product_id' => 2,
                'group_id' => 2,
                'quantity' => 2,
                'subtract' => 1,
                'attribute_value_id' => 1,
                'default' => 2,
                'sort_order' => 1,
            ];
            $product->validate($data);
        } catch (ValidationException $e) {
            $errors = $product->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
