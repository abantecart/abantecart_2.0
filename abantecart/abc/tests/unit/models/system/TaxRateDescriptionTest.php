<?php


use abc\models\system\TaxRateDescription;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class TaxRateDescriptionTest extends TestCase
{
    public function testTaxRateDescriptionValidation()
    {
        $tax = new TaxRateDescription();
        $errors = [];
        try {
            $data = [
                'tax_rate_id' => false,
                'language_id' => false,
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
                'tax_rate_id' => 1,
                'language_id' => 1,
            ];
            $tax->validate($data);
        } catch (ValidationException $e) {
            $errors = $tax->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
