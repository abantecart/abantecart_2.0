<?php


use abc\models\system\ExtensionDependency;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ExtensionDependencyTest extends TestCase
{
    public function testExtensionDependencyValidation()
    {
        $extension = new ExtensionDependency();
        $errors = [];
        try {
            $data = [
                'extension_id'           => false,
                'extension_parent_id'                  => false,
            ];
            $extension->validate($data);
        } catch (ValidationException $e) {
            $errors = $extension->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'extension_id'           => 1,
                'extension_parent_id'  => 1,
            ];
            $extension->validate($data);
        } catch (ValidationException $e) {
            $errors = $extension->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
