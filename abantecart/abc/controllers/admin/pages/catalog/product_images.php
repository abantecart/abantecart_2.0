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
use abc\core\engine\AForm;
use abc\models\catalog\Product;

if (!class_exists('abc\core\ABC') || !ABC::env('IS_ADMIN')) {
    header('Location: static_pages/?forbidden=' . basename(__FILE__));
}

class ControllerPagesCatalogProductImages extends AController
{
    public $error = [];

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $productId = $this->request->get['product_id'];

        $this->loadLanguage('catalog/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->loadModel('tool/image');


        $product_info = Product::getProductInfo($productId);
        if (!$product_info) {
            $this->session->data['warning'] = $this->language->get('error_product_not_found');
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }

        $this->view->assign('error_warning', $this->error['warning']);
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->document->initBreadcrumb(
            [
                'href' => $this->html->getSecureURL('index/home'),
                'text' => $this->language->get('text_home'),
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getSecureURL('catalog/product'),
                'text' => $this->language->get('heading_title'),
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getSecureURL('catalog/product/update', '&product_id=' . $productId),
                'text' => $this->language->get('text_edit')
                    . '&nbsp;' . $this->language->get('text_product')
                    . ' - ' . $product_info['name'],
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href'    => $this->html->getSecureURL('catalog/product_images', '&product_id=' . $productId),
                'text'    => $this->language->get('tab_media'),
                'current' => true
            ]
        );

        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

        $this->data['active'] = 'images';
        //load tabs controller
        $tabs_obj = $this->dispatch('pages/catalog/product_tabs', [$this->data]);
        $this->data['product_tabs'] = $tabs_obj->dispatchGetOutput();
        unset($tabs_obj);

        $this->data['button_add_image'] = $this->html->buildButton(
            [
                'text'  => $this->language->get('button_add_image'),
                'style' => 'button1',
            ]
        );

        $this->data['action'] = $this->html->getSecureURL('catalog/product_images', '&product_id=' . $productId);
        $this->data['form_title'] = $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product');
        $this->data['update'] = '';
        $form = new AForm('HS');

        $form->setForm(
            [
                'form_name' => 'productFrm',
                'update'    => $this->data['update'],
            ]
        );

        $this->data['form']['id'] = 'productFrm';
        $this->data['form']['form_open'] = $form->getFieldHtml(
            [
                'type'   => 'form',
                'name'   => 'productFrm',
                'action' => $this->data['action'],
            ]
        );
        $this->data['form']['submit'] = $form->getFieldHtml(
            [
                'type'  => 'button',
                'name'  => 'submit',
                'text'  => $this->language->get('button_save'),
                'style' => 'button1',
            ]
        );
        $this->data['form']['cancel'] = $form->getFieldHtml(
            [
                'type'  => 'button',
                'name'  => 'cancel',
                'text'  => $this->language->get('button_cancel'),
                'style' => 'button2',
            ]
        );
        if ($this->config->get('config_embed_status')) {
            $this->data['embed_url'] = $this->html->getSecureURL('common/do_embed/product',
                '&product_id=' . $productId);
        }
        $this->view->batchAssign($this->data);
        $this->view->assign('help_url', $this->gen_help_url('product_media'));
        $this->addChild('pages/catalog/product_summary', 'summary_form', 'pages/catalog/product_summary.tpl');

        $this->addChild(
            'responses/common/resource_library/get_resources_html',
            'resources_html',
            'responses/common/resource_library_scripts.tpl'
        );
        $resources_scripts = $this->dispatch(
            'responses/common/resource_library/get_resources_scripts',
            [
                'object_name' => 'products',
                'object_id'   => (int)$productId,
                'types'       => ['image', 'audio', 'video', 'pdf', 'archive'],
            ]
        );
        $this->view->assign('resources_scripts', $resources_scripts->dispatchGetOutput());

        $this->processTemplate('pages/catalog/product_images.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}