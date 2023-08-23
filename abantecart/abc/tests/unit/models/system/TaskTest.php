<?php


use abc\models\system\Task;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskValidation()
    {
        $task = new Task();
        $errors = [];
        try {
            $data = [
                'starter'           => false,
                'status'                  => false,
                'progress'             => false,
                'last_result'                  => false,
                'run_interval'  => false,
                'max_execution_time' => false,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(6, $errors);

        $errors = [];
        try {
            $data = [
                'starter'           => 1,
                'status'             => 1,
                'progress'  => 1,
                'last_result' => 1,
                'run_interval'        => 1,
                'max_execution_time'            => 1,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
