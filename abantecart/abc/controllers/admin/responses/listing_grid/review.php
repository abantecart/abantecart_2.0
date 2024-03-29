<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2017 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\engine\AResource;
use abc\core\lib\AError;
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use H;
use stdClass;

class ControllerResponsesListingGridReview extends AController
{
    public $data = [];

    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/review');
        $this->loadModel('catalog/review');
        $this->loadModel('tool/image');

        if (!isset($this->request->get['store_id'])) {
            $this->request->get['store_id'] = (int)$this->session->data['current_store_id'];
        }

        //Prepare filter config
        $filter_params = array_merge(['product_id', 'status', 'store_id'], (array)$this->data['filter_params']);
        $grid_filter_params = array_merge(['name', 'author'], (array)$this->data['grid_filter_params']);

        $filter_form = new AFilter(['method' => 'get', 'filter_params' => $filter_params]);
        $filter_grid = new AFilter(['method' => 'post', 'grid_filter_params' => $grid_filter_params]);

        $total = $this->model_catalog_review->getTotalReviews(
            array_merge($filter_form->getFilterData(), $filter_grid->getFilterData())
        );

        $response = new stdClass();
        $response->page = $filter_grid->getParam('page');
        $response->total = $filter_grid->calcTotalPages($total);
        $response->records = $total;

        $results = $this->model_catalog_review->getReviews(
            array_merge(
                $filter_form->getFilterData(),
                $filter_grid->getFilterData()
            )
        );

        $product_ids = [];
        foreach ($results as $result) {
            $product_ids[] = (int)$result['product_id'];
        }

        $resource = new AResource('image');
        $thumbnails = $resource->getMainThumbList(
            'products',
            $product_ids,
            $this->config->get('config_image_grid_width'),
            $this->config->get('config_image_grid_height')
        );
        $i = 0;
        foreach ($results as $result) {
            $thumbnail = $thumbnails[$result['product_id']];
            $response->rows[$i]['id'] = $result['review_id'];
            $response->rows[$i]['cell'] = [
                $thumbnail['thumb_html'],
                $result['name'],
                $result['author'],
                $result['rating'],
                $this->html->buildCheckbox([
                    'name'  => 'status['.$result['review_id'].']',
                    'value' => $result['status'],
                    'style' => 'btn_switch',
                ]),
                H::dateISO2Display($result['date_added'], $this->language->get('date_format_short')),
            ];
            $i++;
        }
        $this->data['response'] = $response;
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    public function update()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (!$this->user->canModify('listing_grid/review')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/review'),
                    'reset_value' => true,
                ]);
        }

        $this->loadModel('catalog/review');
        $this->loadLanguage('catalog/review');

        switch ($this->request->post['oper']) {
            case 'del':
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $this->model_catalog_review->deleteReview($id);
                    }
                }
                break;
            case 'save':
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $data = ['status' => $this->request->post['status'][$id],];
                        $this->model_catalog_review->editReview($id, $data);
                    }
                }
                break;

            default:
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    /**
     * update only one field
     *
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \abc\core\lib\AException
     */
    public function update_field()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (!$this->user->canModify('listing_grid/review')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/review'),
                    'reset_value' => true,
                ]);
        }

        $this->loadLanguage('catalog/review');
        $this->loadModel('catalog/review');
        $allowedFields =
            array_merge(['status', 'author', 'product_id', 'text', 'rating'], (array)$this->data['allowed_fields']);

        if (isset($this->request->get['id'])) {
            //request sent from edit form. ID in url
            foreach ($this->request->post as $key => $value) {
                if (!in_array($key, $allowedFields)) {
                    continue;
                }
                $data = [$key => $value];
                $this->model_catalog_review->editReview($this->request->get['id'], $data);
            }
            return null;
        }

        //request sent from jGrid. ID is key of array
        foreach ($this->request->post as $key => $value) {
            if (!in_array($key, $allowedFields)) {
                continue;
            }
            foreach ($value as $k => $v) {
                $data = [$key => $v];
                $this->model_catalog_review->editReview($k, $data);
            }
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

}
