<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\ResourceDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ResourceDescriptionTest extends TestCase
{
    public function testValidator()
    {
        $resource = new ResourceDescription();
        $errors = [];
        try {
            $data = [
                'resource_id'           => false,
                'language_id'                  => false,
            ];
            $resource->validate($data);
        } catch (ValidationException $e) {
            $errors = $resource->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'resource_id'           => 1,
                'language_id'  => 1,

            ];
            $resource->validate($data);
        } catch (ValidationException $e) {
            $errors = $resource->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
