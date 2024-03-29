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
use abc\core\lib\AConnect;
use abc\core\lib\AError;
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use abc\core\lib\ATaskManager;
use H;
use stdClass;

class ControllerResponsesListingGridTask extends AController
{

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->loadLanguage('tool/task');
        if (!$this->user->canAccess('tool/task')) {

            $response = new stdClass();
            $response->userdata = new stdClass();
            $response->userdata->error = sprintf($this->language->get('error_permission_access'), 'tool/task');
            $this->load->library('json');
            $this->response->setOutput(AJson::encode($response));
            return null;
        }


        $page = $this->request->post ['page']; // get the requested page
        $limit = $this->request->post ['rows']; // get how many rows we want to have into the grid

        //Prepare filter config
        $grid_filter_params = array_merge(['name'], (array)$this->data['grid_filter_params']);
        $filter = new AFilter(['method' => 'post', 'grid_filter_params' => $grid_filter_params]);
        $filter_data = $filter->getFilterData();

        $tm = new ATaskManager();
        $total = $tm->getTotalTasks($filter_data);
        if ($total > 0) {
            $total_pages = ceil($total / $limit);
        } else {
            $total_pages = 0;
        }
        $results = $tm->getTasks($filter_data);
        //push result into public scope to get access from extensions
        $this->data['results'] = $results;

        $response = new stdClass ();
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $total;
        $response->userdata = new stdClass();

        $i = 0;
        foreach ($results as $result) {
            $id = $result ['task_id'];
            $response->rows [$i] ['id'] = $id;
            $status = $result['status'];
            //if task works more than 30min - we think it's stuck
            if ($status == 2 && time() - H::dateISO2Int($result['start_time']) > 1800) {
                $status = -1;
            }

            switch ($status) {
                case -1: // stuck
                    $response->userdata->classes[$id] = 'warning disable-run disable-edit disable-restart';
                    $text_status = $this->language->get('text_stuck');
                    break;
                case $tm::STATUS_READY:
                    $response->userdata->classes[$id] = 'success disable-restart  disable-continue disable-edit';
                    $text_status = $this->language->get('text_ready');
                    break;
                case $tm::STATUS_RUNNING:
                    //disable all buttons for running tasks
                    $response->userdata->classes[$id] = 'attention disable-run disable-continue disable-restart disable-edit disable-delete';
                    $text_status = $this->language->get('text_running');
                    break;
                case $tm::STATUS_FAILED:
                    $response->userdata->classes[$id] = 'attention disable-run disable-restart';
                    $text_status = $this->language->get('text_failed');
                    break;
                case $tm::STATUS_SCHEDULED:
                    $response->userdata->classes[$id] = 'success disable-restart disable-continue disable-edit';
                    $text_status = $this->language->get('text_scheduled');
                    break;
                case $tm::STATUS_COMPLETED:
                    $response->userdata->classes[$id] = 'disable-run disable-continue disable-edit';
                    $text_status = $this->language->get('text_completed');
                    break;
                case $tm::STATUS_INCOMPLETE:
                    $response->userdata->classes[$id] = 'disable-run disable-restart disable-edit';
                    $text_status = $this->language->get('text_incomplete');
                    break;
                default: // disabled
                    $response->userdata->classes[$id] = 'attention disable-run disable-restart disable-continue disable-edit disable-delete';
                    $text_status = $this->language->get('text_disabled');
            }

            $response->rows [$i] ['cell'] = [
                $result ['task_id'],
                $result ['name'],
                $text_status,
                H::dateISO2Display($result ['start_time'], $this->language->get('date_format_short') . ' ' . $this->language->get('time_format')),
                H::dateISO2Display($result ['date_modified'], $this->language->get('date_format_short') . ' ' . $this->language->get('time_format')),
            ];
            $i++;
        }
        $this->data['response'] = $response;

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));

    }

    public function restart()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->load->library('json');
        $this->load->language('tool/task');
        $this->response->addJSONHeader();
        $task_id = (int)$this->request->post_or_get('task_id');
        $tm = new ATaskManager();
        $task = $tm->getTaskById($task_id);

        if (!$task_id || !$task) {
            $err = new AError('Task runtime error');
            $err->toJSONResponse(
                'APP_ERROR_402',
                ['error_text' => $this->language->get('text_task_not_found')]
            );
            return;
        }

        //remove task without steps
        if (!$task['steps']) {
            $tm->deleteTask($task_id);
            $err = new AError('Task runtime error');
            $err->toJSONResponse(
                'APP_ERROR_402',
                ['error_text' => $this->language->get('text_empty_task')]
            );
            return;
        }

        //check status
        if (!in_array($task['status'],
            [
                $tm::STATUS_RUNNING,
                $tm::STATUS_FAILED,
                $tm::STATUS_COMPLETED,
                $tm::STATUS_INCOMPLETE
            ])
        ) {
            $err = new AError('Task runtime error');
            $err->toJSONResponse(
                'APP_ERROR_402',
                ['error_text' => $this->language->get('text_forbidden_to_restart')]
            );
            return;
        }


        //if some of steps have sign for interruption on fail - restart whole task
        if ($this->request->get['continue']) {
            $restart_all = false;
            foreach ($task['steps'] as $step) {
                if ($step['settings']['interrupt_on_step_fault'] === true) {
                    $restart_all = true;
                    break;
                }
            }
        } else {
            $restart_all = true;
        }

        //mark all remained step as ready for run
        foreach ($task['steps'] as $step) {
            if ($restart_all || (!$restart_all && in_array($step['status'], [$tm::STATUS_FAILED, $tm::STATUS_INCOMPLETE]))) {
                $tm->updateStep($step['step_id'], ['status' => $tm::STATUS_READY]);
            }
        }

        $tm->updateTask(
            $task_id,
            [
                'status'     => $tm::STATUS_READY,
                'start_time' => date('Y-m-d H:i:s')
            ]
        );
        $this->_run_task($task_id, (!$restart_all ? 'continue' : ''));

        $this->data['output'] = '{}';
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->response->setOutput($this->data['output']);
    }

    public function run()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();

        if (H::has_value($this->request->post_or_get('task_id'))) {
            $tm = new ATaskManager();
            $task = $tm->getTaskById($this->request->post_or_get('task_id'));
            $task_id = null;
            //check
            if ($task && $task['status'] == $tm::STATUS_READY) {
                $tm->updateTask(
                    $task['task_id'],
                    [
                        'start_time' => date('Y-m-d H:i:s'),
                    ]);
                $task_id = $task['task_id'];
            }
            $this->_run_task($task_id);
        } else {
            $this->response->setOutput(AJson::encode(['result' => false]));
        }
        $this->data['output'] = '{}';
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->response->setOutput($this->data['output']);
    }

    // run task in separate process
    private function _run_task($task_id = 0, $run_mode = '')
    {
        $connect = new AConnect(true);
        $url = $this->config->get('config_url')
            . 'task.php?mode=html&task_api_key=' . $this->config->get('task_api_key');
        if ($task_id) {
            $url .= '&task_id=' . $task_id;
        }
        if ($run_mode) {
            $url .= '&run_mode=' . $run_mode;
        }
        $connect->getDataHeaders($url);
        session_write_close();
    }

}