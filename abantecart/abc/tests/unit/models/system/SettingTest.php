<?php


use abc\models\system\Setting;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class SettingTest extends TestCase
{
    public function testSettingValidation()
    {
        $setting = new Setting();
        $errors = [];
        try {
            $data = [
                'store_id'           => false,
            ];
            $setting->validate($data);
        } catch (ValidationException $e) {
            $errors = $setting->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'store_id'           => 1,
            ];
            $setting->validate($data);
        } catch (ValidationException $e) {
            $errors = $setting->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
