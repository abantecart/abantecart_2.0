<?php


use abc\models\system\TaskStep;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class TaskStepTest extends TestCase
{
    public function testTaskStepValidation()
    {
        $task = new TaskStep();
        $errors = [];
        try {
            $data = [
                'task_id' => false,
                'sort_order' => false,
                'status' => false,
                'last_result' => false,
                'max_execution_time' => false,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(5, $errors);

        $errors = [];
        try {
            $data = [
                'task_id' => 1,
                'sort_order' => 1,
                'status' => 1,
                'last_result' => 1,
                'max_execution_time' => 1,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
