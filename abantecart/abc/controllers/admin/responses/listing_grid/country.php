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
use abc\models\customer\Address;
use stdClass;

class ControllerResponsesListingGridCountry extends AController
{
    public $data = [];

    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('localisation/country');
        $this->loadModel('localisation/country');

        //Prepare filter config
        $grid_filter_params =
            array_merge(
                ['name' => 'cd.name', 'iso_code_2' => 'c.iso_code_2', 'iso_code_3' => 'c.iso_code_3'],
                (array)$this->data['grid_filter_params']);
        $filter = new AFilter(['method' => 'post', 'grid_filter_params' => $grid_filter_params]);

        $total = $this->model_localisation_country->getTotalCountries($filter->getFilterData());
        $response = new stdClass();
        $response->page = $filter->getParam('page');
        $response->total = $filter->calcTotalPages($total);
        $response->records = $total;
        $results = $this->model_localisation_country->getCountries($filter->getFilterData());

        $i = 0;
        $language_id = $this->language->getContentLanguageID();

        foreach ($results as $result) {

            $response->rows[$i]['id'] = $result['country_id'];
            $response->rows[$i]['cell'] = [
                $this->html->buildInput([
                    'name'  => 'country_name['.$result['country_id'].']['.$language_id.'][name]',
                    'value' => $result['name'],
                ]),
                $this->html->buildInput([
                    'name'  => 'iso_code_2['.$result['country_id'].']',
                    'value' => $result['iso_code_2'],
                ]),
                $this->html->buildInput([
                    'name'  => 'iso_code_3['.$result['country_id'].']',
                    'value' => $result['iso_code_3'],
                ]),
                $this->html->buildCheckbox([
                    'name'  => 'status['.$result['country_id'].']',
                    'value' => $result['status'],
                    'style' => 'btn_switch',
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

        $this->loadModel('localisation/country');
        $this->loadLanguage('localisation/country');
        if (!$this->user->canModify('listing_grid/country')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/country'),
                    'reset_value' => true,
                ]);
        }

        switch ($this->request->post['oper']) {
            case 'del':
                $this->loadModel('setting/store');
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $err = $this->_validateDelete($id);
                        if (!empty($err)) {
                            $error = new AError('');
                            return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                        }
                        $this->model_localisation_country->deleteCountry($id);
                    }
                }
                break;
            case 'save':
                $allowedFields =
                    array_merge(['iso_code_2', 'iso_code_3', 'status'], (array)$this->data['allowed_fields']);
                $ids = explode(',', $this->request->post['id']);

                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        foreach ($allowedFields as $f) {
                            if ($f == 'status' && !isset($this->request->post['status'][$id])) {
                                $this->request->post['status'][$id] = 0;
                            }

                            if (isset($this->request->post[$f][$id])) {
                                $err = $this->_validateField($f, $this->request->post[$f][$id]);
                                if (!empty($err)) {
                                    $this->response->setOutput($err);
                                    return null;
                                }
                                $this->model_localisation_country->editCountry($id,
                                    [$f => $this->request->post[$f][$id]]);
                            }

                        }

                        if (isset($this->request->post['country_name'][$id])) {
                            foreach ($this->request->post['country_name'][$id] as $lang => $value) {
                                $err = $this->_validateField('name', $value['name']);
                                if (!empty($err)) {
                                    $this->response->setOutput($err);
                                    return null;
                                }
                            }
                            $this->model_localisation_country->editCountry($id,
                                ['country_name' => $this->request->post['country_name'][$id]]);
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

        $this->loadLanguage('localisation/country');
        if (!$this->user->canModify('listing_grid/country')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/country'),
                    'reset_value' => true,
                ]);
        }

        $this->loadModel('localisation/country');
        if (isset($this->request->get['id'])) {
            //request sent from edit form. ID in url
            foreach ($this->request->post as $key => $value) {
                $err = '';
                if ($key == 'country_name') {
                    foreach ($value as $lang => $dvalue) {
                        $err .= $this->_validateField('name', $dvalue['name']);
                    }
                } else {
                    $err = $this->_validateField($key, $value);
                }
                if (!empty($err)) {
                    $error = new AError('');
                    return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                }
                $data = [$key => $value];
                $this->model_localisation_country->editCountry($this->request->get['id'], $data);
            }
            return null;
        }

        //request sent from jGrid. ID is key of array
        $fields = ['iso_code_2', 'iso_code_3', 'status'];
        foreach ($fields as $f) {
            if (isset($this->request->post[$f])) {
                foreach ($this->request->post[$f] as $k => $v) {
                    $err = $this->_validateField($f, $v);
                    if (!empty($err)) {
                        $error = new AError('');
                        return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                    }
                    $this->model_localisation_country->editCountry($k, [$f => $v]);
                }
            }
        }
        if (isset($this->request->post['country_name'])) {
            foreach ($this->request->post['country_name'] as $id => $v) {
                foreach ($v as $lang => $value) {
                    $err = $this->_validateField('name', $value['name']);
                    if (!empty($err)) {
                        $error = new AError('');
                        return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                    }
                }
                $this->model_localisation_country->editCountry($id, ['country_name' => $v]);
            }
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    private function _validateField($field, $value)
    {
        $err = '';
        switch ($field) {
            case 'name' :
                if (mb_strlen($value) < 2 || mb_strlen($value) > 128) {
                    $err = $this->language->get('error_name');
                }
                break;
        }

        return $err;
    }

    private function _validateDelete($country_id)
    {

        $this->loadModel('setting/store');
        $this->loadModel('localisation/zone');
        $this->loadModel('localisation/location');

        if ($this->config->get('config_country_id') == $country_id) {
            return $this->language->get('error_default');
        }

        $store_total = $this->model_setting_store->getTotalStoresByCountryId($country_id);
        if ($store_total) {
            return sprintf($this->language->get('error_store'), $store_total);
        }

        $address_total = Address::where('country_id', '=', $country_id)->get()->count();
        if ( $address_total ) {
            return sprintf($this->language->get('error_address'), $address_total);
        }

        $zone_total = $this->model_localisation_zone->getTotalZonesByCountryId($country_id);
        if ($zone_total) {
            return sprintf($this->language->get('error_zone'), $zone_total);
        }

        $zone_to_location_total = $this->model_localisation_location->getTotalZoneToLocationByCountryID($country_id);
        if ($zone_to_location_total) {
            return sprintf($this->language->get('error_zone_to_location'), $zone_to_location_total);
        }
    }

}
