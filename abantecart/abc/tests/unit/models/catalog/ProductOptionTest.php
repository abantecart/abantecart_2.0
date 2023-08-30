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

use abc\models\catalog\ProductOption;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class ProductOptionTest extends ATestCase
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
        }
        $this->assertCount(0, $errors);
    }
}
