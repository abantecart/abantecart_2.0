<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\ResourceLibrary;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ResourceLibraryTest extends TestCase
{
    public function testValidator()
    {
        $resource = new ResourceLibrary();
        $errors = [];
        try {
            $data = [
                'type_id'           => false,
                'stage_id'                  => false,
            ];
            $resource->validate($data);
        } catch (ValidationException $e) {
            $errors = $resource->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'type_id'           => 1,
                'stage_id'  => 1,
            ];
            $resource->validate($data);
        } catch (ValidationException $e) {
            $errors = $category->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
