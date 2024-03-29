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
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use stdClass;

class ControllerResponsesListingGridLocation extends AController
{
    public $data = [];

    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('localisation/location');
        $this->loadModel('localisation/location');

        //Prepare filter config
        $grid_filter_params = array_merge(['name'], (array)$this->data['grid_filter_params']);

        $filter = new AFilter(['method' => 'post', 'grid_filter_params' => $grid_filter_params]);

        $total = $this->model_localisation_location->getTotalLocations($filter->getFilterData());
        $response = new stdClass();
        $response->page = $filter->getParam('page');
        $response->total = $filter->calcTotalPages($total);
        $response->records = $total;
        $results = $this->model_localisation_location->getLocations($filter->getFilterData());

        $i = 0;
        foreach ($results as $result) {

            $response->rows[$i]['id'] = $result['location_id'];
            $response->rows[$i]['cell'] = [
                $this->html->buildInput([
                    'name'  => 'name['.$result['location_id'].']',
                    'value' => $result['name'],
                ]),
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

        $this->loadModel('localisation/location');
        $this->loadLanguage('localisation/location');
        if (!$this->user->canModify('listing_grid/location')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'),
                        'listing_grid/listing_grid/location'),
                    'reset_value' => true,
                ]);
        }

        switch ($this->request->post['oper']) {
            case 'del':
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $err = $this->_validateDelete($id);
                        if (!empty($err)) {
                            $error = new AError('');
                            return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                        }
                        $this->model_localisation_location->deleteLocation($id);
                    }
                }
                break;
            case 'save':
                $fields = ['name',];
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        foreach ($fields as $f) {
                            if (isset($this->request->post[$f][$id])) {
                                $err = $this->_validateField($f, $this->request->post[$f][$id]);
                                if (!empty($err)) {
                                    $error = new AError('');
                                    return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                                }
                                $this->model_localisation_location->editLocation($id,
                                    [$f => $this->request->post[$f][$id]]);
                            }
                        }
                    }
                }

                break;

            default:
                //print_r($this->request->post);

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

        $this->loadLanguage('localisation/location');
        if (!$this->user->canModify('listing_grid/location')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/location'),
                    'reset_value' => true,
                ]);
        }

        $this->loadModel('localisation/location');
        if (isset($this->request->get['id'])) {
            //request sent from edit form. ID in url
            foreach ($this->request->post as $key => $value) {
                $err = $this->_validateField($key, $value);
                if (!empty($err)) {
                    $error = new AError('');
                    return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                }
                $data = [$key => $value];
                $this->model_localisation_location->editLocation($this->request->get['id'], $data);
            }
            return null;
        }

        //request sent from jGrid. ID is key of array
        $allowedFields = array_merge(['name'], (array)$this->data['allowed_fields']);
        foreach ($allowedFields as $f) {
            if (isset($this->request->post[$f])) {
                foreach ($this->request->post[$f] as $k => $v) {
                    $err = $this->_validateField($f, $v);
                    if (!empty($err)) {
                        $error = new AError('');
                        return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                    }
                    $this->model_localisation_location->editLocation($k, [$f => $v]);
                }
            }
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
    public function update_location_field()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('localisation/location');
        if (!$this->user->canModify('listing_grid/location')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/location'),
                    'reset_value' => true,
                ]);
        }

        $this->loadModel('localisation/location');
        if (isset($this->request->get['id'])) {
            //request sent from edit form. ID in url
            foreach ($this->request->post as $key => $value) {
                $err = $this->_validateField($key, $value);
                if (!empty($err)) {
                    $error = new AError('');
                    return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                }
                $data = [$key => $value];
                $this->model_localisation_location->editLocationZone($this->request->get['id'], $data);
            }
            return null;
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    private function _validateField($field, $value)
    {
        $err = '';
        switch ($field) {
            case 'name' :
                if (mb_strlen($value) < 2 || mb_strlen($value) > 32) {
                    $err = $this->language->get('error_name');
                }
                break;
            case 'description' :
                if (mb_strlen($value) < 2 || mb_strlen($value) > 32) {
                    $err = $this->language->get('error_description');
                }
                break;
        }

        return $err;
    }

    private function _validateDelete($location_id)
    {

        $this->loadModel('localisation/tax_class');
        $tax_rate_total = $this->model_localisation_tax_class->getTotalTaxRatesByLocationID($location_id);
        if ($tax_rate_total) {
            return sprintf($this->language->get('error_tax_rate'), $tax_rate_total);
        }
    }

}
