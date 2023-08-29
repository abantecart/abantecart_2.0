<?php


use abc\models\system\EmailTemplate;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class EmailTemplateTest extends TestCase
{
    public function testEmailTemplateValidation()
    {
        $template = new EmailTemplate();
        $errors = [];
        try {
            $data = [
                'language_id' => false,
            ];
            $template->validate($data);
        } catch (ValidationException $e) {
            $errors = $template->errors()['validation'];
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'language_id' => 1,

            ];
            $template->validate($data);
        } catch (ValidationException $e) {
            $errors = $template->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
