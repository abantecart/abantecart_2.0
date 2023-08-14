<?php

namespace Tests\unit\models\customer;

use abc\models\customer\CustomerNotes;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class CustomerNotesTest extends TestCase
{
    public function testValidator()
    {
        $customer = new CustomerNotes(
            [
                'customer_id'=>-1,
                'user_id'             => -1,
                'stage_id'      => false,
            ]
        );
        $errors = [];
        try {
            $customer->validate();
        } catch (ValidationException $e) {
            $errors =  $customer->errors()['validation'];
            //var_dump($errors);
        }

        $this->assertCount(3, $errors);


        $customer = new CustomerNotes(
            [
                'customer_id'=>1,
                'user_id'=> 1,
                'stage_id'      => 1,
            ]
        );
        $errors = [];
        try {
            $customer->validate();
        } catch (ValidationException $e) {
            $errors =  $customer->errors()['validation'];
            //var_dump($errors);
        }
        $this->assertCount(0, $errors);

    }
}
