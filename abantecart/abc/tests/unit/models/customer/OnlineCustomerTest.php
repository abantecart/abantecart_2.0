<?php

namespace Tests\unit\models\customer;

use abc\models\customer\OnlineCustomer;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class OnlineCustomerTest extends TestCase
{
    public function testValidator()
    {
        $customer = new OnlineCustomer(
            [
                'customer_id' => -1,

            ]
        );
        $errors = [];
        try {
            $customer->validate();
        } catch (ValidationException $e) {
            $errors = $customer->errors()['validation'];
            //var_dump($errors);
        }

        $this->assertCount(1, $errors);


        $customer = new OnlineCustomer(
            [
                'customer_id' => 1,
            ]
        );
        $errors = [];
        try {
            $customer->validate();
        } catch (ValidationException $e) {
            $errors = $customer->errors()['validation'];
            //var_dump($errors);
        }
        $this->assertCount(0, $errors);

    }
}
