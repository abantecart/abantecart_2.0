<?php


use abc\models\system\Job;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    public function testJobValidation()
    {
        $job = new Job();
        $errors = [];
        try {
            $data = [
                'status'           => false,
                'last_result'                  => false,
                'actor_type'             => false,
                'actor_id'                  => false,
            ];
            $job->validate($data);
        } catch (ValidationException $e) {
            $errors = $job->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(4, $errors);

        $errors = [];
        try {
            $data = [
                'category_id'           => 1,
                'uuid'                  => 'uuiidddd',
                'parent_id'             => 36,
                'path'                  => '36_22',
                'total_products_count'  => 1,
                'active_products_count' => 1,
                'children_count'        => 0,
                'sort_order'            => 1,
                'status'                => true,
            ];
            $job->validate($data);
        } catch (ValidationException $e) {
            $errors = $job->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
