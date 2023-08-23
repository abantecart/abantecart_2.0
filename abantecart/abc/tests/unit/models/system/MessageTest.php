<?php


use abc\models\system\Message;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testMessageValidation()
    {
        $massage = new Message();
        $errors = [];
        try {
            $data = [
                'viewed'           => false,
                'repeated'                  => false,
            ];
            $massage->validate($data);
        } catch (ValidationException $e) {
            $errors = $massage->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'viewed'           => 1,
                'repeated' => 1,
            ];
            $massage->validate($data);
        } catch (ValidationException $e) {
            $errors = $massage->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
