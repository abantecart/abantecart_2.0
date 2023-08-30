<?php

namespace Tests\unit\models\system;
use abc\models\system\EmailTemplate;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class EmailTemplateTest extends ATestCase
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
        }
        $this->assertCount(0, $errors);
    }
}
