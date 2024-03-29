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
use abc\core\engine\AForm;
use abc\core\lib\ALayoutManager;
use abc\models\admin\ModelCatalogManufacturer;
use abc\models\catalog\Manufacturer;
use H;

/**
 * Class ControllerPagesCatalogManufacturerLayout
 *
 * @package abc\controllers\admin
 *
 * @property ModelCatalogManufacturer $model_catalog_manufacturer
 */
class ControllerPagesCatalogManufacturerLayout extends AController
{
    public function main()
    {
        $page_controller = 'pages/product/manufacturer';
        $page_key_param = 'manufacturer_id';
        $manufacturer_id = (int)$this->request->get['manufacturer_id'];
        $this->data['manufacturer_id'] = $manufacturer_id;
        $page_url = $this->html->getSecureURL('catalog/manufacturer_layout', '&manufacturer_id='.$manufacturer_id);

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/manufacturer');
        $this->loadLanguage('design/layout');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->loadModel('catalog/manufacturer');
        $manufacturer_info = (new Manufacturer())->getManufacturer($manufacturer_id);

        $this->data['help_url'] = $this->gen_help_url('manufacturer_layout');

        if (H::has_value($manufacturer_id) && $this->request->is_GET()) {
            if (!$manufacturer_info) {
                $this->session->data['warning'] = $this->language->get('error_manufacturer_not_found');
                abc_redirect($this->html->getSecureURL('catalog/manufacturer'));
            }
        }

        $this->data['heading_title'] = $this->language->get('text_edit')
            .$this->language->get('text_manufacturer')
            .' - '.$manufacturer_info['name'];
        $this->data['manufacturer_edit'] = $this->html->getSecureURL(
            'catalog/manufacturer/update',
            '&manufacturer_id='.$manufacturer_id
        );
        if($manufacturer_id) {
            $this->data['active'] = 'layout';
            //load tabs controller
            $tabs_obj = $this->dispatch('pages/catalog/manufacturer_tabs', [$this->data]);
            $this->data['tabs'] = $tabs_obj->dispatchGetOutput();
            unset($tabs_obj);
        }

        // Alert messages
        if (isset($this->session->data['warning'])) {
            $this->data['error_warning'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        }
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $this->document->initBreadcrumb([
            'href'      => $this->html->getSecureURL('index/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
        ]);
        $this->document->addBreadcrumb([
            'href'      => $this->html->getSecureURL('catalog/manufacturer'),
            'text'      => $this->language->get('heading_title'),
            'separator' => ' :: ',
        ]);
        $this->document->addBreadcrumb([
            'href'      => $this->data['manufacturer_edit'],
            'text'      => $this->data['heading_title'],
            'separator' => ' :: ',
            'current'   => true,
        ]);

        $this->data['active'] = 'layout';

        $tmpl_id = $this->request->get['tmpl_id'] ?? $this->config->get('config_storefront_template');
        $layout = new ALayoutManager($tmpl_id);
        //get existing page layout or generic
        $page_layout = $layout->getPageLayoutIDs($page_controller, $page_key_param, $manufacturer_id);
        $page_id = (int)$page_layout['page_id'];
        $layout_id = (int)$page_layout['layout_id'];

        $params = [
            'manufacturer_id' => $manufacturer_id,
            'page_id'         => $page_id,
            'layout_id'       => $layout_id,
            'tmpl_id'         => $tmpl_id,
        ];
        $url = '&'.$this->html->buildURI($params);

        // get templates
        $this->data['templates'] = [];
        $directories = glob(ABC::env('DIR_STOREFRONT').'view/*', GLOB_ONLYDIR);
        foreach ($directories as $directory) {
            $this->data['templates'][] = basename($directory);
        }
        $enabled_templates = $this->extensions->getExtensionsList([
            'filter' => 'template',
            'status' => 1,
        ]);
        foreach ($enabled_templates->rows as $template) {
            $this->data['templates'][] = $template['key'];
        }

        $action = $this->html->getSecureURL('catalog/manufacturer_layout/save');
        // Layout form data
        $form = new AForm('HT');
        $form->setForm([
            'form_name' => 'layout_form',
        ]);

        $this->data['form_begin'] = $form->getFieldHtml([
            'type'   => 'form',
            'name'   => 'layout_form',
            'attr'   => 'data-confirm-exit="true"',
            'action' => $action,
        ]);

        $this->data['hidden_fields'] = '';
        foreach ($params as $name => $value) {
            $this->data[$name] = $value;
            $this->data['hidden_fields'] .= $form->getFieldHtml([
                'type'  => 'hidden',
                'name'  => $name,
                'value' => $value,
            ]);
        }

        $this->data['page_url'] = $page_url;
        $this->data['current_url'] = $this->html->getSecureURL('catalog/manufacturer_layout', $url);

        // insert external form of layout
        $layout = new ALayoutManager($tmpl_id, $page_id, $layout_id);

        $layoutform = $this->dispatch('common/page_layout', [$layout]);
        $this->data['layoutform'] = $layoutform->dispatchGetOutput();

        //build pages and available layouts for cloning
        $this->data['pages'] = $layout->getAllPages();
        $av_layouts = ["0" => $this->language->get('text_select_copy_layout')];
        foreach ($this->data['pages'] as $page) {
            if ($page['layout_id'] != $layout_id) {
                $av_layouts[$page['layout_id']] = $page['layout_name'];
            }
        }

        $form = new AForm('HT');
        $form->setForm([
            'form_name' => 'cp_layout_frm',
        ]);

        $this->data['cp_layout_select'] = $form->getFieldHtml([
            'type'    => 'selectbox',
            'name'    => 'layout_change',
            'value'   => '',
            'options' => $av_layouts,
        ]);

        $this->data['cp_layout_frm'] = $form->getFieldHtml([
            'type'   => 'form',
            'name'   => 'cp_layout_frm',
            'attr'   => 'class="aform form-inline"',
            'action' => $action,
        ]);
        if ($this->config->get('config_embed_status')) {
            $this->view->assign('embed_url',
                $this->html->getSecureURL('common/do_embed/manufacturers', '&manufacturer_id='.$manufacturer_id));
        }

        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/catalog/manufacturer_layout.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function save()
    {
        if ($this->request->is_GET()) {
            abc_redirect($this->html->getSecureURL('catalog/manufacturer_layout'));
        }

        $page_controller = 'pages/product/manufacturer';
        $page_key_param = 'manufacturer_id';
        $manufacturer_id = (int)$this->request->post['manufacturer_id'];

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->loadLanguage('catalog/manufacturer');

        if (!H::has_value($manufacturer_id)) {
            $this->session->data['error'] = $this->language->get('error_product_not_found');
            abc_redirect($this->html->getSecureURL('catalog/manufacturer/update'));
        }

        $post = $this->request->post;
        $templateTextId = $post['tmpl_id'];
        // need to know unique page existing
        $layout = new ALayoutManager($templateTextId);
        $pages = $layout->getPages($page_controller, $page_key_param, $manufacturer_id);
        if (count($pages)) {
            $page_id = $pages[0]['page_id'];
            $layout_id = $pages[0]['layout_id'];
        } else {

            $page_info = [
                'controller' => $page_controller,
                'key_param'  => $page_key_param,
                'key_value'  => $manufacturer_id,
            ];

            $languages = $this->language->getAvailableLanguages();
            $this->loadModel('catalog/manufacturer');
            $manufacturer_info = (new Manufacturer())->getManufacturer($manufacturer_id);
            if ($manufacturer_info) {
                foreach ($languages as $l) {
                    $page_info['page_descriptions'][$l['language_id']] = $manufacturer_info;
                }
            }
            $page_id = $layout->savePage($page_info);
            $layout_id = null;

            // need to generate layout name
            $post['layout_name'] = 'Manufacturer: ' . $manufacturer_info['name'];
        }

        //create new instance with specific template/page/layout data
        $layout = new ALayoutManager($templateTextId, $page_id, $layout_id);
        if (H::has_value($post['layout_change'])) {
            //update layout request. Clone source layout
            $layout->clonePageLayout($post['layout_change'], $layout_id, $post['layout_name']);
            $this->session->data['success'] = $this->language->get('text_success_layout');
        } else {
            //save new layout
            $layout_data = $layout->prepareInput($post);
            if ($layout_data) {
                $layout->savePageLayout($layout_data);
                $this->session->data['success'] = $this->language->get('text_success_layout');
            }
        }
        abc_redirect($this->html->getSecureURL('catalog/manufacturer_layout', '&manufacturer_id='.$manufacturer_id));
    }
}