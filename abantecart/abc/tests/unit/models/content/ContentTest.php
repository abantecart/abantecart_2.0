<?php


use abc\models\content\Content;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{
    public function testValidator()
    {
        $content = new Content();
        $errors = [];
        try {
            $data = [
                'content_id'           => false,
                'parent_id'                  => false,
                'sort_order'             => false,
            ];
            $content->validate($data);
        } catch (ValidationException $e) {
            $errors = $content->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'content_id'           => 1,
                'parent_id'             => 36,
                'sort_order'            => 1,
            ];
            $content->validate($data);
        } catch (ValidationException $e) {
            $errors = $content->errors()['validation'];
           // var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
