<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\GlobalAttributesTypeDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class GlobalAttributesTypeDescriptionTest extends TestCase
{
    public function testValidator()
    {
        $attr = new GlobalAttributesTypeDescription();
        $errors = [];
        try {
            $data = [
                'attribute_type_id'           => false,
                'language_id'                  => false,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'attribute_type_id'           => 1,
                'language_id'             => 36,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
