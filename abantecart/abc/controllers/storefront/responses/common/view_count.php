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
namespace abc\controllers\storefront;
use abc\core\engine\AController;
use abc\models\catalog\Product;

class ControllerResponsesCommonViewCount extends AController {

	public function main(){}
  	public function product() {

		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
        Product::find((int)$this->request->get['product_id'])
            ?->increment('viewed');
        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
  	}
}