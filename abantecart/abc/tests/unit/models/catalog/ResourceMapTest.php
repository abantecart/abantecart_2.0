<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\ResourceMap;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ResourceMapTest extends TestCase
{
    public function testValidator()
    {
        $resource = new ResourceMap();
        $errors = [];
        try {
            $data = [
                'resource_id'           => false,
                'object_id'                  => false,
                'sort_order'             => false,
            ];
            $resource->validate($data);
        } catch (ValidationException $e) {
            $errors = $resource->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'resource_id'           => 1,
                'object_id'                  =>1,
                'sort_order'            => 1,
            ];
            $resource->validate($data);
        } catch (ValidationException $e) {
            $errors = $resource->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
