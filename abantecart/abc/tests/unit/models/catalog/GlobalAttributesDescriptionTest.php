<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\GlobalAttributesDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class GlobalAttributesDescriptionTest extends TestCase
{
    public function testValidator()
    {
        $attr = new GlobalAttributesDescription();
        $errors = [];
        try {
            $data = [
                'attribute_id' => false,
                'language_id'  => false,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(9, $errors);

        $errors = [];
        try {
            $data = [
                'attribute_id' => 1,
                'language_id'  => 1,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
