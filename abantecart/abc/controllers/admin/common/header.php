<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2018 Belavier Commerce LLC

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

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\lib\AMenu;
use abc\core\lib\AResourceManager;
use abc\models\admin\ModelToolOnlineNow;
use abc\models\customer\Customer;
use H;

/**
 * Class ControllerCommonHeader
 *
 * @property ModelToolOnlineNow $model_tool_online_now
 *
 */
class ControllerCommonHeader extends AController
{
    public function main()
    {
        //use to init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->load->helper('html');
        $this->view->assign('breadcrumbs', $this->document->getBreadcrumbs());

        if ($this->request->is_POST() && isset($this->request->post['language_code'])) {
            unset($this->session->data['content_language']);
            $this->session->data['language'] = $this->request->post['language_code'];

            if (!empty($this->request->post['redirect'])) {
                abc_redirect($this->request->post['redirect']);
            } else {
                abc_redirect($this->html->getURL('index/home'));
            }
        }

        $this->view->assign('language_code', $this->session->data['language']);
        $this->view->assign('languages', $this->language->getActiveLanguages());
        $this->view->assign('content_language_id', $this->language->getContentLanguageID());
        $this->view->assign('language_settings', $this->html->getSecureURL('localisation/language'));

        $this->view->assign('new_messages', $this->messages->getShortList());
        $this->view->assign('messages_link', $this->html->getSecureURL('tool/message_manager'));

        $this->view->assign('action', $this->html->getSecureURL('index/home'));
        $this->view->assign('search_action', $this->html->getSecureURL('tool/global_search'));

        //redirect after language change
        if (!$this->request->get['rt'] || $this->request->get['rt'] == 'index/home') {
            $this->view->assign('redirect', $this->html->getSecureURL('index/home'));
            $this->view->assign('home_page', true);
        } else {
            $this->view->assign('home_page', false);
            $this->view->assign('redirect', ABC::env('HTTPS_SERVER').'?'.$_SERVER['QUERY_STRING']);
        }

        if (!$this->user->isLogged()
            || !isset($this->request->get['token'])
            || !isset($this->session->data['token'])
            || ($this->request->get['token'] != $this->session->data['token'])
        ) {
            $this->view->assign('logged', '');
            $this->view->assign('home', $this->html->getSecureURL('index/login', '', true));
        } else {
            $this->view->assign('home', $this->html->getSecureURL('index/home', '', true));
            $this->view->assign('logged', sprintf($this->language->get('text_logged'), $this->user->getUserName()));
            $this->view->assign('avatar', $this->user->getAvatar());
            $this->view->assign('username', $this->user->getUserName());
            if ($this->user->getLastLogin()) {
                $this->view->assign(
                    'last_login',
                    sprintf($this->language->get('text_last_login'),
                        H::dateISO2Display($this->user->getLastLogin(),
                            $this->language->get('date_format_short')))
                );
            } else {
                $this->view->assign(
                    'last_login',
                    sprintf($this->language->get('text_welcome'), $this->user->getUserName())
                );
            }
            $this->view->assign('account_edit', $this->html->getSecureURL('index/edit_details', '', true));
            $this->view->assign(
                'im_settings_edit',
                $this->html->getSecureURL('user/user/im', '&user_id='.$this->user->getId(), true)
            );
            $this->view->assign('text_edit_notifications', $this->language->get('text_edit_notifications'));

            $stores = [];
            $this->loadModel('setting/store');
            $results = $this->model_setting_store->getStores();
            foreach ($results as $result) {
                $stores[] = [
                    'name' => $result['name'],
                    'href' => $result['config_url'],
                ];
            }
            $this->view->assign('stores', $stores);

            $this->view->assign('logout', $this->html->getSecureURL('index/logout'));
            $this->view->assign('store', $this->config->get('config_url'));
            // add dynamic menu based on dataset scheme
            $this->addChild('common/menu', 'menu', 'common/menu.tpl');

            //Get current menu item
            $menu = new AMenu('admin');

            $current_menu = $menu->getMenuByRT($this->request->get['rt']);
            if (!$current_menu && substr_count($this->request->get['rt'], '/') >= 2) {
                $rt_parts = explode('/', $this->request->get['rt']);
                if ($rt_parts) {
                    //remove p,a,r prefixes from rt
                    if (strlen($rt_parts[0]) == 1) {
                        unset($rt_parts[0]);
                    }
                    //try to get icon from parent menu item
                    array_pop($rt_parts);
                    $menu_item = $menu->getMenuByRT(implode('/', $rt_parts));
                    if ($menu_item['item_icon_rl_id']) {
                        $current_menu = ['item_icon_rl_id' => $menu_item['item_icon_rl_id']];
                    }
                }
                unset($rt_parts, $menu_item);
            }
            if ($current_menu ['item_icon_rl_id']) {
                $rm = new AResourceManager();
                $rm->setType('image');
                $resource = $rm->getResource($current_menu ['item_icon_rl_id']);
                $current_menu['icon'] = $resource['resource_code'];
            }
            unset($current_menu['item_icon_rl_id']);
            $this->view->assign('current_menu', $current_menu);
        }
        if ($this->user->isLogged()) {
            $ant_message = $this->messages->getANTMessage();
            $this->view->assign('ant', $ant_message['html']);
            $this->view->assign(
                'mark_read_url',
                $this->html->getSecureURL('common/common/antMessageRead', '&message_id='.$ant_message['id'])
            );

            $this->view->assign('ant_viewed', $ant_message['viewed']);
        }
        $this->view->assign('config_voicecontrol', $this->config->get('config_voicecontrol'));
        $this->view->assign('voicecontrol_setting_url', $this->html->getSecureURL('setting/setting/system'));
        $this->view->assign('command_lookup_url', $this->html->getSecureURL('common/action_commands'));
        $this->view->assign(
            'search_suggest_url',
            $this->html->getSecureURL('listing_grid/global_search_result/suggest')
        );
        $this->view->assign('latest_customers_url', $this->html->getSecureURL('common/tabs/latest_customers'));
        $this->view->assign('latest_orders_url', $this->html->getSecureURL('common/tabs/latest_orders'));
        $this->view->assign('rl_manager_url', $this->html->getSecureURL('tool/rl_manager'));

        $this->view->assign('server_date', date($this->language->get('date_format_short')));
        $this->view->assign('server_time', date($this->language->get('time_format')));

        $this->view->assign('search_everywhere', $this->language->get('search_everywhere'));
        $this->view->assign('text_all_matches', $this->language->get('text_all_matches'));
        $this->view->assign('dialog_title', $this->language->get('text_quick_edit_form'));
        $this->view->assign('button_go', $this->html->buildButton(
            [
                'name'  => 'searchform_go',
                'text'  => $this->language->get('button_go'),
                'style' => 'button5',
            ]
        )
        );


        //prepare quick stats
        $this->loadModel('tool/online_now');
        $online_new = $this->model_tool_online_now->getTotalTodayOnline('new');
        $online_registered = $this->model_tool_online_now->getTotalTodayOnline('registered');
        $this->view->assign('online_new', $online_new);
        $this->view->assign('online_registered', $online_registered);

        $this->loadModel('report/sale');
        $data = [
            'filter' => [
                'order_status' => 'confirmed',
                'date_start'   => H::dateISO2Display(date('Y-m-d', time()), $this->language->get('date_format_short')),
                'date_end'     => H::dateISO2Display(date('Y-m-d', time()), $this->language->get('date_format_short')),
            ],
        ];

        $today_orders = $this->model_report_sale->getSaleReportSummary($data);
        $today_order_count = $today_orders['orders'];
        $today_sales_amount = $this->currency->format(
            $today_orders['total_amount'],
            $this->config->get('config_currency')
        );

        $this->view->assign('today_order_count', $today_order_count);
        $this->view->assign('today_sales_amount', $today_sales_amount);

        $today_customer_count = Customer::getTotalCustomers(
            [
                'filter' =>
                    [
                        'date_added' => date('Y-m-d', time()),
                    ],
            ]
        );
        $this->view->assign('today_customer_count', $today_customer_count);

        if($this->config->get('config_maintenance')){
            $this->view->assign('maintenance_warning',$this->language->get('text_maintenance_notice'));
        }

        $this->loadModel('catalog/review');
        $today_review_count = $this->model_catalog_review->getTotalToday();
        $this->view->assign('today_review_count', $today_review_count);

        $this->processTemplate('common/header.tpl');
        //use to update data before render
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}