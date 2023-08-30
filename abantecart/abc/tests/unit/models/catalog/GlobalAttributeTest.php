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

use abc\models\catalog\GlobalAttribute;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class GlobalAttributeTest extends ATestCase
{
    public function testValidator()
    {
        $attr = new GlobalAttribute();
        $errors = [];
        try {
            $data = [
                'attribute_parent_id' => 0,
                'attribute_group_id'  => 0,
                'attribute_type_id'   => 0,
                'sort_order'          => 0,
                'required'            => 0,
                'status'              => 0,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(6, $errors);

        $errors = [];
        try {
            $data = [
                'attribute_parent_id' => 1,
                'attribute_group_id'  => 1,
                'attribute_type_id'   => 1,
                'sort_order'          => 1,
                'required'            => 1,
                'status'              => 1,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
