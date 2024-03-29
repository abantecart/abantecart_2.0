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
use abc\core\lib\AError;
use abc\core\lib\AException;
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use abc\models\customer\Customer;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use stdClass;

class ControllerResponsesListingGridCustomerGroup extends AController
{
    public $data = [];

    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('sale/customer_group');
        $this->loadModel('sale/customer_group');

        //Prepare filter config
        $grid_filter_params = array_merge(['name', 'tax_exempt'], (array)$this->data['grid_filter_params']);
        $filter = new AFilter(['method' => 'post', 'grid_filter_params' => $grid_filter_params]);
        $total = $this->model_sale_customer_group->getTotalCustomerGroups($filter->getFilterData());

        $response = new stdClass();
        $response->page = $filter->getParam('page');
        $response->total = $filter->calcTotalPages($total);
        $response->records = $total;
        $results = $this->model_sale_customer_group->getCustomerGroups($filter->getFilterData());

        $i = 0;
        $yesno = [
            1 => $this->language->get('text_yes'),
            0 => $this->language->get('text_no'),
        ];

        foreach ($results as $result) {

            $response->rows[$i]['id'] = $result['customer_group_id'];
            $response->rows[$i]['cell'] = [
                $result['name'].(($result['customer_group_id']
                    == $this->config->get('config_customer_group_id')) ? $this->language->get('text_default') : null),
                $yesno[(int)$result['tax_exempt']],
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

        if (!$this->user->canModify('listing_grid/customer_group')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'),
                        'listing_grid/customer_group'),
                    'reset_value' => true,
                ]);
        }

        $this->loadLanguage('sale/customer_group');
        $this->loadModel('sale/customer_group');
        $this->loadModel('setting/store');
        switch ($this->request->post['oper']) {
            case 'del':
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {

                        if ($this->config->get('config_customer_group_id') == $id) {
                            $this->response->setOutput($this->language->get('error_default'));
                            return null;
                        }

                        $store_total = $this->model_setting_store->getTotalStoresByCustomerGroupId($id);
                        if ($store_total) {
                            $this->response->setOutput(sprintf($this->language->get('error_store'), $store_total));
                            return null;
                        }

                        $customer_total = Customer::getTotalCustomers(['filter' => ['customer_group_id' => $id]]);
                        if ($customer_total) {
                            $this->response->setOutput(sprintf($this->language->get('error_customer'),
                                $customer_total));
                            return null;
                        }

                        $this->model_sale_customer_group->deleteCustomerGroup($id);
                    }
                }
                break;
            case 'save':
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
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws AException
     */
    public function update_field()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (!$this->user->canModify('listing_grid/customer_group')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'),
                        'listing_grid/customer_group'),
                    'reset_value' => true,
                ]);
        }

        $this->loadLanguage('sale/customer_group');
        $this->loadModel('sale/customer_group');

        if (isset($this->request->get['id'])) {
            $this->model_sale_customer_group->editCustomerGroup($this->request->get['id'], $this->request->post);
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

}