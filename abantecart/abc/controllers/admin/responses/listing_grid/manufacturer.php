<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2023 Belavier Commerce LLC

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
use abc\core\lib\AException;
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use abc\models\admin\ModelCatalogManufacturer;
use abc\models\catalog\Manufacturer;
use abc\models\catalog\Product;
use H;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use stdClass;

class ControllerResponsesListingGridManufacturer extends AController
{
    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/manufacturer');
        $this->loadModel('catalog/manufacturer');
        $this->loadModel('tool/image');

        //Prepare filter config
        $grid_filter_params = array_merge(['name'], (array)$this->data['grid_filter_params']);
        $filter = new AFilter(['method' => 'post', 'grid_filter_params' => $grid_filter_params]);
        $filter_data = $filter->getFilterData();

        $total = $this->model_catalog_manufacturer->getTotalManufacturers($filter_data);
        $response = new stdClass();
        $response->page = $filter->getParam('page');
        $response->total = $filter->calcTotalPages($total);
        $response->records = $total;
        $results = $this->model_catalog_manufacturer->getManufacturers($filter_data);

        //build thumbnails list
        $resource = new AResource('image');
        $thumbnails = $resource->getMainThumbList(
            'manufacturers',
            array_column($results, 'manufacturer_id'),
            $this->config->get('config_image_grid_width'),
            $this->config->get('config_image_grid_height')
        );

        $i = 0;
        foreach ($results as $result) {
            $thumbnail = $thumbnails[$result['manufacturer_id']];
            $response->rows[$i]['id'] = $result['manufacturer_id'];
            $response->rows[$i]['cell'] = [
                $thumbnail['thumb_html'],
                $this->html->buildInput(
                    [
                        'name'  => 'name[' . $result['manufacturer_id'] . ']',
                        'value' => $result['name'],
                    ]
                ),
                $this->html->buildInput(
                    [
                        'name'  => 'sort_order[' . $result['manufacturer_id'] . ']',
                        'value' => $result['sort_order'],
                    ]
                ),
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
        if (!$this->user->canModify('listing_grid/manufacturer')) {
            $error = new AError('');
            $error->toJSONResponse(
                'NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf(
                        $this->language->get('error_permission_modify'),
                        'listing_grid/manufacturer'
                    ),
                    'reset_value' => true,
                ]
            );
            return;
        }

        $this->loadLanguage('catalog/manufacturer');

        switch ($this->request->post['oper']) {
            case 'del':
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $err = $this->validateDelete((int)$id);
                        if (!empty($err)) {
                            $error = new AError('');
                            $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                            return;
                        }
                        Manufacturer::deleteManufacturer($id);
                    }
                }
                break;
            case 'save':
                $allowedFields = array_merge(['sort_order', 'name'], (array)$this->data['allowed_fields']);
                $ids = explode(',', $this->request->post['id']);
                $array = [];
                if (!empty($ids)) //resort required.
                {
                    if ($this->request->post['resort'] == 'yes') {
                        //get only ids we need
                        foreach ($ids as $id) {
                            $array[$id] = $this->request->post['sort_order'][$id];
                        }
                        $new_sort = H::build_sort_order(
                            $ids,
                            min($array),
                            max($array),
                            $this->request->post['sort_direction']
                        );
                        $this->request->post['sort_order'] = $new_sort;
                    }
                }
                foreach ($ids as $id) {
                    foreach ($allowedFields as $field) {
                        Manufacturer::editManufacturer(
                            $id,
                            [$field => $this->request->post[$field][$id]]
                        );
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
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws AException
     */
    public function update_field()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (!$this->user->canModify('listing_grid/manufacturer')) {
            $error = new AError('');

            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/manufacturer'),
                    'reset_value' => true,
                ]);
        }
        $this->loadLanguage('catalog/manufacturer');
        $this->loadModel('catalog/manufacturer');

        if (isset($this->request->get['id'])) {
            //request sent from edit form. ID in url
            foreach ($this->request->post as $field => $value) {
                if ($field == 'keyword') {
                    if ($err = $this->html->isSEOkeywordExists('manufacturer_id=' . $this->request->get['id'], $value)) {
                        $error = new AError('');

                        return $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                    }
                }

                (new Manufacturer())->editManufacturer($this->request->get['id'], [$field => $value]);
            }

            return null;
        }

        //request sent from jGrid. ID is key of array
        foreach ($this->request->post as $field => $value) {
            foreach ($value as $k => $v) {
                (new Manufacturer())->editManufacturer($k, [$field => $v]);
            }
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function manufacturers()
    {
        $response = [];
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->loadModel('catalog/manufacturer');
        if (isset($this->request->post['term'])) {
            $filter = [
                'limit'         => 20,
                'language_id'   => $this->language->getContentLanguageID(),
                'subsql_filter' => "m.name LIKE '%" . $this->db->escape($this->request->post['term'], true) . "%'",
            ];
            $results = $this->model_catalog_manufacturer->getManufacturers($filter);

            //build thumbnails list
            $ids = array_column($results, 'manufacturer_id');

            $resource = new AResource('image');
            $thumbnails = $resource->getMainThumbList(
                'manufacturers',
                $ids,
                $this->config->get('config_image_grid_width'),
                $this->config->get('config_image_grid_height')
            );
            foreach ($results as $item) {
                $thumbnail = $thumbnails[$item['manufacturer_id']];

                $response[] = [
                    'image' => $thumbnail['thumb_html'] ?: '<i class="fa fa-code fa-4x"></i>&nbsp;',
                    'id'         => $item['manufacturer_id'],
                    'name'       => $item['name'],
                    'meta'       => '',
                    'sort_order' => (int)$item['sort_order'],
                ];
            }
        }
        $this->data['response'] = $response;

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    protected function validateDelete(int $manufacturerId)
    {
        $this->data['error'] = '';
        Manufacturer::setCurrentLanguageID($this->language->getDefaultLanguageID());
        $products = Product::where('manufacturer_id', '=', $manufacturerId)->get();
        $productCount = $products->count();
        if ($productCount) {
            $this->data['error'] = sprintf($this->language->get('error_product'), $productCount);
            if ($productCount < 10) {
                $this->data['error'] .= ' ID(s): ' . implode(', ', $products->pluck('product_id')->toArray());
            }
        }
        $this->extensions->hk_ValidateData($this, __FUNCTION__, $manufacturerId);
        return $this->data['error'];
    }
}