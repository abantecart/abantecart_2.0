<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\GlobalAttributesValueDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class GlobalAttributesValueDescriptionTest extends TestCase
{
    public function testValidator()
    {
        $attr = new GlobalAttributesValueDescription();
        $errors = [];
        try {
            $data = [
                'attribute_value_id'           => false,
                'attribute_id'                  => false,
                'language_id'             => false,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'attribute_value_id'           => 1,
                'attribute_id'             => 36,
                'language_id'                  => 1,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
