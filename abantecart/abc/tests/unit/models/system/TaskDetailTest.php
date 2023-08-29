<?php


use abc\models\system\TaskDetail;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class TaskDetailTest extends TestCase
{
    public function testTaskDetailValidation()
    {
        $task = new TaskDetail();
        $errors = [];
        try {
            $data = [
                'created_by' => false,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'created_by' => 1,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
