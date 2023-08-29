<?php


use abc\models\system\EncryptionKey;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class EncryptionKeyTest extends TestCase
{
    public function testEncryptionKeyValidation()
    {
        $encription = new EncryptionKey();
        $errors = [];
        try {
            $data = [
                'status' => false,
            ];
            $encription->validate($data);
        } catch (ValidationException $e) {
            $errors = $encription->errors()['validation'];
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'status' => 1,
            ];
            $encription->validate($data);
        } catch (ValidationException $e) {
            $errors = $encription->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
