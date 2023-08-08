<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\GlobalAttributesValue;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class GlobalAttributesValueTest extends TestCase
{
    public function testValidator()
    {
        $attr = new GlobalAttributesValueTest();
        $errors = [];
        try {
            $data = [
                'attribute_id'           => false,
                'sort_order'             => false,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'attribute_id'           => 1,
                'sort_order'            => 1,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
