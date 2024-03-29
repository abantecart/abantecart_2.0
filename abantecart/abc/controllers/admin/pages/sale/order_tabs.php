<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2021 Belavier Commerce LLC

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
use abc\core\lib\AException;
use abc\models\order\OrderDownload;

class ControllerPagesSaleOrderTabs extends AController
{
    public function main($data = [])
    {
        $this->data = $data;
        if (!is_array($this->data)) {
            throw new AException (
                'Error: Could not create order tabs. Tabs definition is not array.',
                AC_ERR_LOAD
            );
        }
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('sale/order');
        $order_id = $this->request->get['order_id'];
        $this->data['order_id'] = $order_id;
        $this->data['groups'] = ['order_details', 'shipping', 'payment'];

        $this->data['link_order_details'] = $this->html->getSecureURL('sale/order/details', '&order_id='.$order_id);
        $this->data['link_shipping'] = $this->html->getSecureURL('sale/order/shipping', '&order_id='.$order_id);
        $this->data['link_payment'] = $this->html->getSecureURL('sale/order/payment', '&order_id='.$order_id);

        $download_count = OrderDownload::where('order_id', '=', $order_id)->count();
        if ($download_count) {
            $this->data['link_files'] = $this->html->getSecureURL('sale/order/files', '&order_id='.$order_id);
            $this->data['groups'][] = 'files';
        }
        $this->data['link_history'] = $this->html->getSecureURL('sale/order/history', '&order_id='.$order_id);
        $this->data['groups'][] = 'history';

        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/sale/order_tabs.tpl');

        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}

