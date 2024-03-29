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

use abc\core\ABC;
use abc\core\engine\AController;

class ControllerPagesIndexLogout extends AController {
    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->user->logout();
        unset($this->session->data['token'], $this->session->data['system_check_last_time']);
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        //expire session cookie
        setcookie(ABC::env('SESSION_ID'), '', 1);
        abc_redirect($this->html->getSecureURL('index/login'));
    }
}