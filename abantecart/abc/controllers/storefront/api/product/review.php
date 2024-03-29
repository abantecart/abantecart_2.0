<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2022 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\controllers\storefront;

use abc\core\engine\AControllerAPI;
use abc\models\storefront\ModelCatalogReview;
use H;


/**
 * Class ControllerApiProductReview
 *
 * @package abc\controllers\storefront
 * @property ModelCatalogReview $model_catalog_review
 */
class ControllerApiProductReview extends AControllerAPI
{

    //TODO: Incorrect Logic, to review on a call
    public function get()
    {
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $product_id = $this->request->get['product_id'];
        if (!$product_id) {
            $this->rest->setResponseData(['Error' => 'Missing product ID as a required parameter']);
            $this->rest->sendResponse(200);
            return null;
        }

        if (!$this->config->get('enable_reviews')) {
            $this->rest->setResponseData(['Error' => 'Reviews for products are disabled']);
            $this->rest->sendResponse(200);
            return null;
        }

        $this->loadModel('catalog/review');
        $total_reviews = $this->model_catalog_review->getTotalReviewsByProductId($product_id);
        $average = $this->model_catalog_review->getAverageRating($product_id);

        $page = $this->request->get['page'] ?? 1;

        $rows = $this->request->get['rows'] ?? 5;

        if ($total_reviews > 0 && $rows > 0) {
            $total_pages = ceil($total_reviews / $rows);
        } else {
            $total_pages = 0;
        }

        $reviews = [];
        $results = $this->model_catalog_review->getReviewsByProductId($product_id, ($page - 1) * $rows, $rows);
        foreach ($results as $result) {
            $reviews[] = [
                'author'     => $result['author'],
                'rating'     => $result['rating'],
                'text'       => strip_tags($result['text']),
                'date_added' => H::dateISO2Display($result['date_added'],
                    $this->language->get('date_format_short')),
            ];
        }

        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData([
            'average' => $average,
            'records' => $total_reviews,
            'page'    => $page,
            'total'   => $total_pages,
            'rows'    => $reviews,
        ]);
        $this->rest->sendResponse(200);
    }

    public function put()
    {
        //Allow to review only for logged in customers.
        if (!$this->customer->isLoggedWithToken($this->request->get['token'])) {
            $this->rest->setResponseData(['error' => 'Login attempt failed!']);
            $this->rest->sendResponse(401);
            return null;
        }
    }
}
