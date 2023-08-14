<?php


use abc\models\content\ContentDescription;
use PHPUnit\Framework\TestCase;
use Illuminate\Validation\ValidationException;

class ContentDescriptionTest extends TestCase
{
    public function testValidator()
    {
        $content = new ContentDescription();
        $errors = [];
        try {
            $data = [
                'content_id'           => false,
                'language_id'                  => false,
            ];
            $content->validate($data);
        } catch (ValidationException $e) {
            $errors = $content->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'content_id'           => 1,
                'language_id'             => 36,
            ];
            $content->validate($data);
        } catch (ValidationException $e) {
            $errors = $content->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
