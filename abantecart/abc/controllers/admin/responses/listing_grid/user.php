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
use H;
use stdClass;

class ControllerResponsesListingGridUser extends AController
{
    public $data = [];

    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('user/user');
        $this->loadModel('user/user');

        $this->loadModel('user/user_group');
        $user_groups = ['' => $this->language->get('text_select_group'),];
        $results = $this->model_user_user_group->getUserGroups();
        foreach ($results as $r) {
            $user_groups[$r['user_group_id']] = $r['name'];
        }

        //Prepare filter config
        $filter_params = array_merge(['status', 'user_group_id'], (array)$this->data['filter_params']);
        $grid_filter_params = array_merge(['username'], (array)$this->data['grid_filter_params']);

        //Build query string based on GET params first
        $filter_form = new AFilter(['method' => 'get', 'filter_params' => $filter_params]);
        //Build final filter
        $filter_grid = new AFilter([
            'method'                   => 'post',
            'grid_filter_params'       => $grid_filter_params,
            'additional_filter_string' => $filter_form->getFilterString(),
        ]);
        $total = $this->model_user_user->getTotalUsers($filter_grid->getFilterData());
        $response = new stdClass();
        $response->page = $filter_grid->getParam('page');
        $response->total = $filter_grid->calcTotalPages($total);
        $response->records = $total;
        $results = $this->model_user_user->getUsers($filter_grid->getFilterData());

        $i = 0;
        foreach ($results as $result) {
            $response->rows[$i]['id'] = $result['user_id'];
            $response->rows[$i]['cell'] = [
                $result['username'],
                $user_groups[$result['user_group_id']],
                $this->html->buildCheckbox([
                    'name'  => 'status['.$result['user_id'].']',
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

        if (!$this->user->canModify('listing_grid/user')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/user'),
                    'reset_value' => true,
                ]);
        }

        $this->loadModel('user/user');
        $this->loadLanguage('user/user');

        switch ($this->request->post['oper']) {
            case 'del':
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        if ($this->user->getId() == $id) {
                            $this->response->setOutput($this->language->get('error_account'));
                            return null;
                        }
                        $this->model_user_user->deleteUser($id);
                    }
                }
                break;
            case 'save':
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $this->model_user_user->editUser($id,
                            ['status' => isset($this->request->post['status'][$id]) ? $this->request->post['status'][$id] : 0]);
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

        if (!$this->user->canModify('listing_grid/user')) {
            $error = new AError('');
            return $error->toJSONResponse('NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/user'),
                    'reset_value' => true,
                ]);
        }

        $this->loadLanguage('user/user');
        $this->loadModel('user/user');
        if (isset($this->request->get['id'])) {
            //request sent from edit form. ID in url
            foreach ($this->request->post as $key => $value) {
                if ($key == 'password_confirm') {
                    continue;
                }
                if ($key == 'user_group_id') {
                    $user_info = $this->model_user_user->getUser($this->request->get['id']);
                    if ($user_info['user_group_id'] != $value) {
                        if ( //cannot to change group for yourself
                            $this->request->get['id'] == $this->user->getId()
                            //or current user is not admin
                            || $this->user->getUserGroupId() != 1) {

                            $error = new AError('');
                            return $error->toJSONResponse(
                                'NO_PERMISSIONS_403',
                                [
                                    'error_text'  => $this->language->get('error_user_group'),
                                    'reset_value' => true,
                                ]);
                        }
                    }
                }

                $data = [$key => $value];
                $this->model_user_user->editUser($this->request->get['id'], $data);
            }
            return null;
        }

        //request sent from jGrid. ID is key of array
        foreach ($this->request->post as $field => $value) {
            foreach ($value as $k => $v) {
                $this->model_user_user->editUser($k, [$field => $v]);
            }
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

}
