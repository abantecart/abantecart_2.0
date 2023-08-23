<?php


use abc\models\system\TaxRate;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class TaxRateTest extends TestCase
{
    public function testTaxRateValidation()
    {
        $tax = new TaxRate();
        $errors = [];
        try {
            $data = [
                'location_id'           => false,
                'zone_id'                  => false,
                'tax_class_id'             => false,
                'priority'                  => false,
            ];
            $tax->validate($data);
        } catch (ValidationException $e) {
            $errors = $tax->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(4, $errors);

        $errors = [];
        try {
            $data = [
                'location_id'           => 1,
                'zone_id'             => 1,
                'tax_class_id'  => 1,
                'priority' => 1,

            ];
            $tax->validate($data);
        } catch (ValidationException $e) {
            $errors = $tax->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
