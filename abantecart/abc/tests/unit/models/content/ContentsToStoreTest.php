<?php

namespace Tests\unit\models\content;

use abc\models\content\ContentsToStore;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;


class ContentsToStoreTest extends ATestCase
{
    public function testValidator()
    {
        $content = new ContentsToStore();
        $errors = [];
        try {
            $data = [
                'content_id' => false,
                'store_id' => false,
            ];
            $content->validate($data);
        } catch (ValidationException $e) {
            $errors = $content->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'content_id' => 1,
                'store_id' => 36,
            ];
            $content->validate($data);
        } catch (ValidationException $e) {
            $errors = $content->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
