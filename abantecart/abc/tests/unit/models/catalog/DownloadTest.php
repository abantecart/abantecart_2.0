<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\Download;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class DownloadTest extends TestCase
{
    public function testValidator()
    {
        $download = new Download();
        $errors = [];
        try {
            $data = [
                'max_downloads'           => false,
                'expire_days'                  => false,
                'sort_order'             => false,
                'activate_order_status_id'                  => false,
                'shared'  => false,
                'status' => false,

            ];
            $download->validate($data);
        } catch (ValidationException $e) {
            $errors = $download->errors()['validation'];
            var_Dump($errors);
        }
        $this->assertCount(9, $errors);

        $errors = [];
        try {
            $data = [
                'max_downloads'           => 1,
                'expire_days'                  => 1,
                'sort_order'             => 1,
                'activate_order_status_id'                  => 1,
                'shared'  => 1,
                'status' => 1,
            ];
            $download->validate($data);
        } catch (ValidationException $e) {
            $errors = $download->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
