<?php

namespace Tests\unit\core\lib;

use abc\core\engine\ModelsForm;
use abc\models\catalog\Category;
use abc\models\catalog\Product;
use Tests\unit\ATestCase;

class FormTest extends ATestCase
{

    public function testLoadModelFields()
    {
        $initData = [
            'abc\models\catalog\Product'  => ['weight', 'height'],
            'abc\models\catalog\Category' => [],
        ];

        $product = new Product();
        $productFields = $product->getFields();
        $category = new Category();
        $categoryFields = $category->getFields();
        $expectedData = [
            'abc\models\catalog\Product'  => ['weight' => $productFields['weight'], 'height' => $productFields['height']],
            'abc\models\catalog\Category' => $categoryFields,
        ];
        $formData = [
            'form_id'      => 'test_form_id',
            'form_name'    => 'test_form',
            'controller'   => null,
            'success_page' => null,
            'status'       => 1,
            'description'  => '',
        ];

        $formInstance = new ModelsForm($initData);
        $fields = $formInstance->getFields();


        $this->assertEquals($expectedData, $fields);
    }
}
