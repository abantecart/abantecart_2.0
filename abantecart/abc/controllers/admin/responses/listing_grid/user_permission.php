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
use abc\core\lib\AError;
use abc\core\lib\AException;
use abc\core\lib\AJson;
use H;
use ReflectionException;
use stdClass;


class ControllerResponsesListingGridUserPermission extends AController
{

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('user/user_group');
        $this->loadModel('user/user_group');

        $page = $this->request->post['page']; // get the requested page
        $limit = $this->request->post['rows']; // get how many rows we want to have into the grid
        $sord = $this->request->post['sord']; // get the direction

        // process jGrid search parameter
        $allowedDirection = ['asc', 'desc'];

        if (!in_array($sord, $allowedDirection)) {
            $sord = $allowedDirection[0];
        }

        $data = [
            'order' => strtoupper($sord),
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
        ];

        $total = $this->model_user_user_group->getTotalUserGroups();
        if ($total > 0) {
            $total_pages = ceil($total / $limit);
        } else {
            $total_pages = 0;
        }

        $response = new stdClass();
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $total;
        $response->userdata = new stdClass();

        $results = $this->model_user_user_group->getUserGroups($data);
        $i = 0;
        foreach ($results as $result) {
            $id = $result['user_group_id'];
            if ($result['user_group_id'] == 1) {
                $response->userdata->hightligth[$id] = '';
                $response->userdata->classes[$id] = 'disable-edit disable-delete';
                $name = $result['name'];
            } else {
                $name = $this->html->buildInput(
                    [
                        'name'  => 'name['.$id.']',
                        'value' => $result['name'],
                    ]
                );
            }
            $response->rows[$i]['id'] = $id;
            $response->rows[$i]['cell'] = [$name];
            $i++;
        }
        $this->data['response'] = $response;

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    /**
     * update only one field
     *
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     * @throws AException
     */
    public function update_field()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('user/user_group');
        if (!$this->user->canModify('listing_grid/user_permission')) {
            $error = new AError('');
            $error->toJSONResponse(
                'NO_PERMISSIONS_403',
                [
                    'error_text'  => sprintf(
                        $this->language->get('error_permission_modify'), 'listing_grid/user_permission'
                    ),
                    'reset_value' => true,
                ]
            );
            return;
        }

        $this->loadModel('user/user_group');

        // update user group name
        // request sent from jGrid. ID is key of array
        $fields = ['name'];
        foreach ($fields as $f) {
            if (isset($this->request->post[$f])) {
                foreach ($this->request->post[$f] as $k => $v) {
                    $err = $this->_validateField($f, $v);
                    if (!empty($err)) {
                        $error = new AError('');
                        $error->toJSONResponse('VALIDATION_ERROR_406', ['error_text' => $err]);
                        return;
                    }
                    $this->model_user_user_group->editUserGroup($k, [$f => $v]);
                }
            }
        }

        // update user group permissions

        if (H::has_value($this->request->post['permission']) && H::has_value($this->request->get['user_group_id'])) {
            $this->model_user_user_group->editUserGroup($this->request->get['user_group_id'], $this->request->post);
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    private function _validateField($field, $value)
    {
        $err = '';
        switch ($field) {
            case 'name' :
                if (isset($value) && ((mb_strlen($value) < 2) || (mb_strlen($value) > 64))) {
                    $err = $this->language->get('error_name');
                }
                break;
        }

        return $err;
    }

    public function getPermissions()
    {
        $user_group_id = (int) $this->request->get['user_group_id'];
        $this->loadLanguage('user/user_group');
        $this->loadModel('user/user_group');
        // check user_group_id for
        $result = $this->model_user_user_group->getUserGroup($user_group_id);
        $permissions = $result['permission'];
        if (empty($permissions)) {
            $permissions = [
                'access' => [],
                'modify' => [],
            ];
        }

        $page = $this->request->post['page']; // get the requested page
        $limit = $this->request->post['rows']; // get how many rows we want to have into the grid
        $sidx = $this->request->post['sidx']; // get index row - i.e. user click to sort
        $sord = $this->request->post['sord']; // get the direction

        // get controllers list
        $controllers = $this->model_user_user_group->getAllControllers($sord);

        $this->load->library('json');
        $searchData = json_decode(htmlspecialchars_decode($this->request->post['filters']), true);
        $search_str = $searchData['rules'][0]['data'];
        $access = $modify = [];
        foreach ($controllers as $key => $controller) {
            $access[$key] = (int) $permissions['access'][$controller] ?: null;
            $modify[$key] = (int) $permissions['modify'][$controller] ?: null;
        }

        //filter result by controller name (temporary solution). needs to improve.
        foreach ($controllers as $key => $controller) {
            if ($search_str) {
                if (!is_int(strpos($controller, $search_str))) {
                    unset($controllers[$key]);
                }
            }
        }

        // process jGrid search parameter
        $allowedDirection = ['asc', 'desc'];

        if (!in_array($sord, $allowedDirection)) {
            $sord = $allowedDirection[0];
        }

        // resort by permissions
        if ($sidx == 'access') {
            array_multisort($access, ($sord == 'asc' ? SORT_ASC : SORT_DESC), $controllers);
        } elseif ($sidx == 'modify') {
            array_multisort($modify, ($sord == 'asc' ? SORT_ASC : SORT_DESC), $controllers);
        }

        $data = [
            //'order' => strtoupper($sord),
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
        ];

        $total = sizeof($controllers);
        if ($total > 0) {
            $total_pages = ceil($total / $limit);
        } else {
            $total_pages = 0;
        }

        $response = new stdClass();
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $total;
        $response->userdata = new stdClass();

        $i = 0;
        $controllers = array_slice($controllers, $data['start'], $data['limit']);

        foreach ($controllers as $k => $controller) {
            if (!in_array($controller, array_keys($permissions['access']))
                && !in_array($controller, array_keys($permissions['modify']))
            ) {
                $response->userdata->classes[$k] = 'warning';
            }

            $response->rows[$i]['id'] = $k;
            $response->rows[$i]['cell'] = [
                $k + $data['start'] + 1,
                '<a style="padding-left: 10px;" 
                    href="'.$this->html->getSecureURL($controller).'" 
                    target="_blank" 
                    title="'.$this->language->get('text_go_to_page').'">'.$controller.'</a>',
                $this->html->buildCheckbox(
                    [
                       'name'  => 'permission[access]['.$controller.']',
                       'value' => ($permissions['access'][$controller] ? 1 : 0),
                       'style' => 'btn_switch',
                   ]
                ),
                $this->html->buildCheckbox(
                    [
                       'name'  => 'permission[modify]['.$controller.']',
                       'value' => ($permissions['modify'][$controller] ? 1 : 0),
                       'style' => 'btn_switch',
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

}
