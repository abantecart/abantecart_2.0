<?php

namespace abc\modules\workers;

use abc\core\ABC;
use abc\extensions\tims_catalog\modules\traits\ProductExportTrait;
use abc\models\catalog\Category;
use abc\models\catalog\Product;

class FixCategoriesCounters extends ABaseWorker
{
    use ProductExportTrait;
    private $lockFile = 'FixCategoriesCountersWorker.lock';

    public function __construct()
    {
        parent::__construct();
    }

    public function getModuleMethods()
    {
        return ['main'];
    }

    public function postProcessing()
    {
        @unlink($this->lockFile);
    }

    // php abcexec job:run --worker=FixCategoriesCounters [ optional] --touch-products
    public function main($params = [])
    {
        if ($params['touch-products']) {
            $chunkStep = 5;
        } else {
            $chunkStep = 100;
        }

        $this->init();
        Category::chunk(
            $chunkStep,
            static function ($categories) {
                foreach ($categories as $category) {
                    $category = Category::with('products')->find($category->category_id);
                    $category->touch();

                    $ids = $category->products->pluck('product_id')->toArray();
                    $this->processBatch($ids);
//                    foreach ($category->products as $p) {
//                        $product = Product::find($p->product_id);
//                        $product->touch();
//                    }
                }
            }
        );
    }

    private function init(): bool
    {
        $this->lockFile = ABC::env('DIR_SYSTEM').$this->lockFile;
        if (is_file($this->lockFile)) {
            $pid = file_get_contents($this->lockFile);
            exit ('Another worker with process ID '.$pid.' is running. Skipped.');
        }

        return true;
    }

    /**
     * @param string $text
     *
     * @void
     */
    public function echoCli($text)
    {
        if ($this->outputType == 'cli') {
            echo $text.$this->EOF;
        } else {
            $this->output[] = $text;
        }
    }
}