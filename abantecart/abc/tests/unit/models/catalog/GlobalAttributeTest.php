<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\GlobalAttribute;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class GlobalAttributeTest extends TestCase
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
            //var_Dump($errors);
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
