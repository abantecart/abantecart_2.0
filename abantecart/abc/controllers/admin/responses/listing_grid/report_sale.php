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

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use H;
use stdClass;

class ControllerResponsesListingGridReportSale extends AController
{

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('report/sale/orders');
        $this->loadModel('report/sale');

        //Prepare filter config
        $grid_filter_params =
            array_merge(
                [
                    'date_start',
                    'date_end',
                    'group', 
                    'order_status'
                ], 
                (array) $this->data['grid_filter_params']
            );

        $filter_form = new AFilter(['method' => 'get', 'filter_params' => $grid_filter_params]);
        $filter_grid = new AFilter(['method' => 'post']);
        $data = array_merge($filter_form->getFilterData(), $filter_grid->getFilterData());

        $total = $this->model_report_sale->getSaleReportTotal($data);
        $response = new stdClass();
        $response->page = $filter_grid->getParam('page');
        $response->total = $filter_grid->calcTotalPages($total);
        $response->records = $total;

        $results = $this->model_report_sale->getSaleReport($data);
        $i = 0;
        foreach ($results as $result) {
            $response->rows[$i]['id'] = $i;
            $response->rows[$i]['cell'] = [
                H::dateISO2Display($result['date_start'], $this->language->get('date_format_short')),
                H::dateISO2Display($result['date_end'], $this->language->get('date_format_short')),
                $result['orders'],
                $this->currency->format($result['total'], $this->config->get('config_currency')),
            ];
            $i++;
        }

        $this->data['response'] = $response;

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    public function taxes()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('report/sale/taxes');
        $this->loadModel('report/sale');

        //Prepare filter config
        $grid_filter_params = array_merge(
                [
                    'date_start',
                    'date_end',
                    'group',
                    'order_status'
                ],
                (array) $this->data['grid_filter_params']
        );
        $filter_form = new AFilter(['method' => 'get', 'filter_params' => $grid_filter_params]);
        $filter_grid = new AFilter(['method' => 'post']);
        $data = array_merge($filter_form->getFilterData(), $filter_grid->getFilterData());

        $total = $this->model_report_sale->getTaxesReportTotal($data);
        $response = new stdClass();
        $response->page = $filter_grid->getParam('page');
        $response->total = $filter_grid->calcTotalPages($total);
        $response->records = $total;

        $results = $this->model_report_sale->getTaxesReport($data);
        $i = 0;
        foreach ($results as $result) {
            $response->rows[$i]['id'] = $i;
            $response->rows[$i]['cell'] = [
                H::dateISO2Display($result['date_start'], $this->language->get('date_format_short')),
                H::dateISO2Display($result['date_end'], $this->language->get('date_format_short')),
                $result['orders'],
                $this->currency->format($result['total'], $this->config->get('config_currency')),
            ];
            $i++;
        }
        $this->data['response'] = $response;
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    public function shipping()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('report/sale/shipping');
        $this->loadModel('report/sale');

        //Prepare filter config
        $grid_filter_params = array_merge(
            [
                'date_start',
                'date_end',
                'group',
                'order_status'
            ],
            (array) $this->data['grid_filter_params']
        );

        $filter_form = new AFilter(['method' => 'get', 'filter_params' => $grid_filter_params]);
        $filter_grid = new AFilter(['method' => 'post']);
        $data = array_merge($filter_form->getFilterData(), $filter_grid->getFilterData());

        $total = $this->model_report_sale->getShippingReportTotal($data);
        $response = new stdClass();
        $response->page = $filter_grid->getParam('page');
        $response->total = $filter_grid->calcTotalPages($total);
        $response->records = $total;

        $results = $this->model_report_sale->getShippingReport($data);
        $i = 0;
        foreach ($results as $result) {
            $response->rows[$i]['id'] = $i;
            $response->rows[$i]['cell'] = [
                H::dateISO2Display($result['date_start'], $this->language->get('date_format_short')),
                H::dateISO2Display($result['date_end'], $this->language->get('date_format_short')),
                $result['orders'],
                $this->currency->format($result['total'], $this->config->get('config_currency')),
            ];
            $i++;
        }
        $this->data['response'] = $response;
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    public function coupons()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('report/sale/coupons');
        $this->loadModel('report/sale');

        //Prepare filter config
        $grid_filter_params = array_merge(['date_start', 'date_end'], (array) $this->data['grid_filter_params']);
        $filter_form = new AFilter(['method' => 'get', 'filter_params' => $grid_filter_params]);
        $filter_grid = new AFilter(['method' => 'post']);
        $data = array_merge($filter_form->getFilterData(), $filter_grid->getFilterData());

        $total = $this->model_report_sale->getCouponsReportTotal($data);
        $response = new stdClass();
        $response->page = $filter_grid->getParam('page');
        $response->total = $filter_grid->calcTotalPages($total);
        $response->records = $total;

        $results = $this->model_report_sale->getCouponsReport($data);
        $i = 0;
        foreach ($results as $result) {
            $response->rows[$i]['id'] = $i;
            $response->rows[$i]['cell'] = [
                $result['coupon_name'],
                $result['code'],
                $result['orders'],
                $this->currency->format($result['total'], $this->config->get('config_currency')),
                $this->currency->format($result['discount_total'], $this->config->get('config_currency')),
            ];
            $i++;
        }
        $this->data['response'] = $response;
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

}