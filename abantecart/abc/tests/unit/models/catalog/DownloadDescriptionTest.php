<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\DownloadDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class DownloadDescriptionTest extends TestCase
{
    public function testValidator()
    {
        $download = new DownloadDescription();
        $errors = [];
        try {
            $data = [
                'download_id' => false,
                'language_id' => false,
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
                'language_id' => 1,
            ];
            $download->validate($data);
        } catch (ValidationException $e) {
            $errors = $download->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
