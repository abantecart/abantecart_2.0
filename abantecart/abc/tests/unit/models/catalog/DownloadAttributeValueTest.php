<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\DownloadAttributeValue;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class DownloadAttributeValueTest extends TestCase
{
    public function testValidator()
    {
        $download = new DownloadAttributeValue();
        $errors = [];
        try {
            $data = [
                'attribute_id' => false,
                'download_id' => false,
            ];
            $download->validate($data);
        } catch (ValidationException $e) {
            $errors = $download->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(9, $errors);

        $errors = [];
        try {
            $data = [
                'download_id' => 1,
                'attribute_id' => 1,
            ];
            $download->validate($data);
        } catch (ValidationException $e) {
            $errors = $download->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
