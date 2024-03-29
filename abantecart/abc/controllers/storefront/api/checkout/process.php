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

namespace abc\controllers\storefront;

use abc\core\ABC;
use abc\core\engine\AControllerAPI;
use abc\core\engine\ASecureControllerAPI;
use abc\core\lib\AJson;
use abc\core\lib\AOrder;

class ControllerApiCheckoutProcess extends ASecureControllerAPI
{
    public $error = [];

    public function post()
    {
        $request = $this->rest->getRequestParams();

        //Check if confirmation details were reviewed.
        if (!$this->session->data['confirmed']) {
            $this->rest->sendResponse(
                400,
                [
                    'status' => 0,
                    'error'  => 'Need to review confirmation details first!',
                ]
            );
            return null;
        }
        $this->session->data['confirmed'] = false;

        //Check if order is created and process payment
        if (!isset($this->session->data['order_id'])) {
            $this->rest->sendResponse(500,
                ['status' => 2, 'error' => 'Not order data available!']
            );
            return null;
        }

        /**
         * @var AOrder $order
         */
        $order = ABC::getObjectByAlias('AOrder', [$this->registry]);
        $order_data = $order->loadOrderData($this->session->data['order_id'], 'any');
        //Check if order is present and not processed yet
        if (!isset($order_data)) {
            $this->rest->sendResponse(500,
                [
                    'status' => 3,
                    'error'  => 'No order available. Something went wrong!',
                ]
            );
            return null;
        }
        if ($order_data['order_status_id'] > 0) {
            $this->rest->sendResponse(200, ['status' => 4, 'error' => 'Order was already processed!']);
            return null;
        }

        //Dispatch the payment send controller process and capture the result
        if (!$this->session->data['process_rt']) {
            $this->rest->sendResponse(500,
                [
                    'status' => 5,
                    'error'  => 'Something went wrong. Incomplete request!',
                ]
            );
            return null;
        }
        //we process only response type payment extensions
        $payment_controller = $this->dispatch(
            'responses/extension/'.$this->session->data['process_rt'],
            [$request]
        );
        $this->load->library('json');
        $this->data = AJson::decode($payment_controller->dispatchGetOutput(), true);

        if ($this->data['error']) {
            $this->data['status'] = 6;
            $this->rest->sendResponse(200, $this->data);
            return null;
        } else {
            if ($this->data['success']) {
                $this->data['status'] = 1;
                //order completed clean up
                if (isset($this->session->data['order_id'])) {
                    $this->cart->clear();

                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['guest']);
                    unset($this->session->data['comment']);
                    unset($this->session->data['order_id']);
                    unset($this->session->data['coupon']);
                }
                $this->rest->setResponseData($this->data);
                $this->rest->sendResponse(200);
            } else {
                $this->data['status'] = 0;
                $this->data['error'] = "Unexpected Error";
                $this->rest->sendResponse(500, $this->data);
            }
        }
    }
}