<?php


use abc\models\system\TaxClassDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class TaxClassDescriptionTest extends TestCase
{
    public function testValidator()
    {
        $tax = new TaxClassDescription();
        $errors = [];
        try {
            $data = [
                'tax_class_id'           => false,
                'language_id'                  => false,
            ];
            $tax->validate($data);
        } catch (ValidationException $e) {
            $errors = $tax->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'tax_class_id'           => 1,
                'language_id'  => 1,
            ];
            $tax->validate($data);
        } catch (ValidationException $e) {
            $errors = $tax->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
