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
use abc\core\lib\AMenu_Storefront;
use abc\models\catalog\Category;
use abc\models\content\Content;
use H;

class ControllerPagesDesignMenu extends AController
{
    public $error = [];
    protected $columns = [
        'item_id',
        'item_icon',
        'item_icon_rl_id',
        'item_text',
        'item_url',
        'parent_id',
        'sort_order',
    ];
    /** @var AMenu_Storefront */
    protected $menu, $menu_items, $menu_tree;

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->initBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('index/home'),
                'text'      => $this->language->get('text_home'),
                'separator' => false,
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('design/menu'),
                'text'      => $this->language->get('heading_title'),
                'separator' => ' :: ',
                'current'   => true,
            ]
        );

        $this->menu = new AMenu_Storefront();
        $menu_parents = $this->menu->getItemIds();

        $menu_id = ['' => $this->language->get('text_select_parent_id')];
        foreach ($menu_parents as $item) {
            if ($item != '') {
                $menu_id[$item] = $item;
            }
        }

        $grid_settings = [
            'table_id'         => 'menu_grid',
            'url'              => $this->html->getSecureURL(
                'listing_grid/menu',
                '&parent_id=' . $this->request->get['parent_id']
            ),
            'editurl'          => $this->html->getSecureURL('listing_grid/menu/update'),
            'update_field'     => $this->html->getSecureURL('listing_grid/menu/update_field'),
            'sortname'         => 'sort_order',
            'sortorder'        => 'asc',
            'drag_sort_column' => 'sort_order',
            'columns_search'   => false,
            'actions'          => [
                'edit'   => [
                    'text' => $this->language->get('text_edit'),
                    'href' => $this->html->getSecureURL('design/menu/update', '&item_id=%ID%'),
                ],
                'delete' => ['text' => $this->language->get('button_delete')],
                'save'   => ['text' => $this->language->get('button_save')],
            ],
        ];

        $form = new AForm ();
        $form->setForm(['form_name' => 'menu_grid_search']);

        $grid_search_form = [];
        $grid_search_form['id'] = 'menu_grid_search';
        $grid_search_form['form_open'] = $form->getFieldHtml(
            [
                'type'   => 'form',
                'name'   => 'menu_grid_search',
                'action' => '',
            ]
        );
        $grid_search_form['submit'] = $form->getFieldHtml(
            [
                'type' => 'button',
                'name' => 'submit',
                'text' => $this->language->get('button_go'),
            ]
        );

        $grid_search_form['reset'] = $form->getFieldHtml(
            [
                'type' => 'button',
                'name' => 'reset',
                'text' => $this->language->get('button_reset'),
            ]
        );
        $grid_search_form['fields']['parent_id'] = $form->getFieldHtml(
            [
                'type'    => 'selectbox',
                'name'    => 'parent_id',
                'options' => $menu_id,
                'value'   => $this->request->get['parent_id'],
            ]
        );

        $grid_settings['search_form'] = true;

        $grid_settings['colNames'] = [
            '',
            $this->language->get('entry_item_id'),
            $this->language->get('entry_item_text'),
            $this->language->get('entry_sort_order'),
        ];
        $grid_settings['colModel'] = [
            [
                'name'     => 'item_icon',
                'index'    => 'item_icon',
                'width'    => 80,
                'align'    => 'center',
                'sortable' => false,
                'search'   => false,
            ],
            [
                'name'   => 'item_id',
                'index'  => 'item_id',
                'width'  => 120,
                'align'  => 'left',
                'search' => false,
            ],
            [
                'name'   => 'item_text',
                'index'  => 'item_text',
                'width'  => 360,
                'align'  => 'center',
                'search' => false,
            ],
            [
                'name'   => 'sort_order',
                'index'  => 'sort_order',
                'align'  => 'center',
                'search' => false,
            ],
        ];

        if ($this->config->get('config_show_tree_data')) {
            $grid_settings['expand_column'] = "item_id";
            $grid_settings['multiaction_class'] = 'hidden';
        }

        $grid = $this->dispatch('common/listing_grid', [$grid_settings]);
        $this->view->assign('listing_grid', $grid->dispatchGetOutput());
        $this->view->assign('search_form', $grid_search_form);

        $this->view->batchAssign($this->language->getASet());
        $this->view->assign('insert', $this->html->getSecureURL('design/menu/insert'));
        $this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
        $this->view->assign('help_url', $this->gen_help_url('menu_listing'));

        $this->processTemplate('pages/design/menu.tpl');
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function insert()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->document->setTitle($this->language->get('heading_title'));

        $this->menu = new AMenu_Storefront();
        $language_id = $this->language->getContentLanguageID();

        if ($this->request->is_POST() && $this->_validateForm($this->request->post)) {
            $post = $this->request->post;
            $languages = $this->language->getAvailableLanguages();
            foreach ($languages as $l) {
                if ($l['language_id'] == $language_id) {
                    continue;
                }
                $post['item_text'][$l['language_id']] = $post['item_text'][$language_id];
            }

            $post['item_icon'] = html_entity_decode($post['item_icon'], ENT_COMPAT, ABC::env('APP_CHARSET'));
            $text_id = H::preformatTextID($post['item_id']);
            $result = $this->menu->insertMenuItem(
                [
                    'item_id'         => $text_id,
                    'item_icon'       => $post['item_icon'],
                    'item_icon_rl_id' => $post['item_icon_resource_id'],
                    'item_text'       => $post['item_text'],
                    'parent_id'       => $post['parent_id'],
                    'item_url'        => $post['item_url'],
                    'sort_order'      => $post['sort_order'],
                    'item_type'       => 'core',
                ]
            );

            if ($result !== true) {
                $this->error['warning'] = $result;
            } else {
                $this->session->data['success'] = $this->language->get('text_success');
                abc_redirect($this->html->getSecureURL('design/menu/update', '&item_id=' . $text_id));
            }
        }

        $this->_getForm();

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function update()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $item_id = $this->request->get['item_id'];
        $this->document->setTitle($this->language->get('heading_title'));

        $this->menu = new AMenu_Storefront();

        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        if (($this->request->is_POST()) && $this->_validateForm($this->request->post)) {
            $post = $this->request->post;
            if (isset ($post['item_icon'])) {
                $post['item_icon'] = html_entity_decode($post['item_icon'], ENT_COMPAT, ABC::env('APP_CHARSET'));
            }

            $item_keys = [
                'item_icon',
                'item_text',
                'item_url',
                'parent_id',
                'sort_order',
                'item_icon_resource_id',
            ];

            $update_item = [];

            if ($item_id) {
                foreach ($item_keys as $item_key) {
                    if (isset ($post[$item_key])) {
                        $update_item[$item_key] = $post[$item_key];
                    }
                }

                if (H::has_value($update_item['item_icon_resource_id'])) {
                    $update_item['item_icon_rl_id'] = $update_item['item_icon_resource_id'];
                } else {
                    $update_item['item_icon_rl_id'] = '';
                }
                unset($update_item['item_icon_resource_id']);

                // set condition for updating row
                $this->menu->updateMenuItem($item_id, $update_item);
            }

            $this->session->data['success'] = $this->language->get('text_success');
            abc_redirect($this->html->getSecureURL('design/menu/update', '&item_id=' . $item_id));
        }

        $this->_getForm();

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    protected function _getForm()
    {
        if (isset ($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['error'] = $this->error;

        $this->document->initBreadcrumb([
            'href'      => $this->html->getSecureURL('index/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
        ]);
        $this->document->addBreadcrumb([
            'href'      => $this->html->getSecureURL('design/menu'),
            'text'      => $this->language->get('heading_title'),
            'separator' => ' :: ',
        ]);

        $this->data['cancel'] = $this->html->getSecureURL('design/menu');

        $language_id = $this->language->getContentLanguageID();
        $item_id = $this->request->get['item_id'];

        $menu_item = null;
        $parent_id = [];

        $this->menu_items = $this->menu->getMenuItems();
        $this->_buildMenuTree('');

        if ($item_id) {
            unset($this->menu_tree[$item_id]);
        }
        foreach ($this->menu_tree as $item) {
            $parent_id[$item['item_id']] = $item['text'];
        }

        foreach ($this->columns as $column) {
            if (isset ($this->request->post[$column])) {
                $this->data[$column] = $this->request->post[$column];
            } elseif (!empty($menu_item)) {
                $this->data[$column] = $menu_item[$column];
            } else {
                $this->data[$column] = '';
            }
        }

        if (!$item_id) {
            $this->data['action'] = $this->html->getSecureURL('design/menu/insert');
            $this->data['heading_title'] =
                $this->language->get('text_insert') . '&nbsp;' . $this->language->get('heading_title');
            $this->data['update'] = '';
            $form = new AForm ('HT');
        } else {
            //get menu item details
            $this->data = array_merge($this->data, $this->menu->getMenuItem($item_id));

            $this->data['action'] = $this->html->getSecureURL('design/menu/update', '&item_id=' . $item_id);
            $this->data['heading_title'] = $this->language->get('text_edit') . $this->language->get('heading_title');
            $this->data['update'] = $this->html->getSecureURL('listing_grid/menu/update_field', '&id=' . $item_id);
            $form = new AForm ('HS');
        }

        $this->document->addBreadcrumb([
            'href'      => $this->data['action'],
            'text'      => $this->data['heading_title'],
            'separator' => ' :: ',
            'current'   => true,
        ]);

        $form->setForm(['form_name' => 'menuFrm', 'update' => $this->data['update']]);

        $this->data['form']['form_open'] = $form->getFieldHtml([
            'type'   => 'form',
            'name'   => 'menuFrm',
            'attr'   => 'data-confirm-exit="true" class="aform form-horizontal"',
            'action' => $this->data['action'],
        ]);

        $this->data['form']['submit'] = $form->getFieldHtml([
            'type' => 'button',
            'name' => 'submit',
            'text' => $this->language->get('button_save'),
        ]);

        $this->data['form']['cancel'] = $form->getFieldHtml([
            'type' => 'button',
            'name' => 'cancel',
            'text' => $this->language->get('button_cancel'),
        ]);

        $this->data['form']['fields']['item_id'] = $form->getFieldHtml(
            [
                'type'     => 'input',
                'name'     => 'item_id',
                'value'    => $this->data['item_id'],
                'required' => true,
                'attr'     => $item_id ? 'disabled' : '',
            ]
        );

        $this->data['form']['fields']['item_text'] = $form->getFieldHtml(
            [
                'type'         => 'input',
                'name'         => 'item_text[' . $language_id . ']',
                'value'        => $this->data['item_text'][$language_id],
                'required'     => true,
                'style'        => 'large-field',
                'multilingual' => true,
            ]
        );

        $this->data['link_types'] = [
            'category' => $this->language->get('text_category_link_type'),
            'content'  => $this->language->get('text_content_link_type'),
            'custom'   => $this->language->get('text_custom_link_type'),
        ];

        $this->data['link_type'] = $this->html->buildElement(
            [
                'type'    => 'selectbox',
                'name'    => 'link_type',
                'options' => $this->data['link_types'],
                'value'   => '',
                'style'   => 'no-save short-field',
            ]
        );

        $this->data['form']['fields']['item_url'] = $form->getFieldHtml(
            [
                'type'     => 'input',
                'name'     => 'item_url',
                'value'    => $this->data['item_url'],
                'style'    => 'large-field',
                'required' => true,
                'help_url' => $this->gen_help_url('item_url'),
            ]
        );

        $categories = Category::getCategories();
        $options = ['' => $this->language->get('text_select')];
        foreach ($categories as $c) {
            if (!$c['status']) {
                continue;
            }
            $options[$c['category_id']] = $c['name'];
        }
        $this->data['link_category'] = $this->html->buildElement(
            [
                'type'    => 'selectbox',
                'name'    => 'menu_categories',
                'options' => $options,
                'style'   => 'no-save short-field',
            ]
        );


        $options = ['' => $this->language->get('text_select')]
            +
            (array)Content::getContents(
                [
                    'filter' => [
                        'status' => 1
                    ]
                ]
            )?->pluck('name', 'content_id')->toArray();

        $this->data['link_content'] = $this->html->buildElement(
            [
                'type'    => 'selectbox',
                'name'    => 'menu_information',
                'options' => $options,
                'style'   => 'no-save short-field',
            ]
        );

        $this->data['form']['fields']['parent_id'] = $form->getFieldHtml(
            [
                'type'    => 'selectbox',
                'name'    => 'parent_id',
                'options' => array_merge(['' => $this->language->get('text_none')], $parent_id),
                'value'   => $this->data['parent_id'],
                'style'   => 'medium-field',
            ]
        );
        $this->data['form']['fields']['sort_order'] = $form->getFieldHtml(
            [
                'type'  => 'input',
                'name'  => 'sort_order',
                'value' => $this->data['sort_order'],
                'style' => 'small-field',
            ]
        );

        $this->data['form']['fields']['item_icon'] = $form->getFieldHtml([
            'type'          => 'resource',
            'name'          => 'item_icon',
            'resource_path' => htmlspecialchars(
                $this->data['item_icon'], ENT_COMPAT,
                ABC::env('APP_CHARSET')
            ),
            'resource_id'   => $this->data['item_icon_rl_id'],
            'rl_type'       => 'image',
        ]);
        //add scripts for RL work
        $resources_scripts = $this->dispatch(
            'responses/common/resource_library/get_resources_scripts',
            [
                'object_name' => 'storefront_menu_item',
                'object_id'   => (int)$this->request->get['item_id'],
                'types'       => ['image'],
                'onload'      => true,
                'mode'        => 'single',
            ]
        );
        $this->data['resources_scripts'] = $resources_scripts->dispatchGetOutput();

        $this->view->batchAssign($this->language->getASet());
        $this->view->batchAssign($this->data);
        $this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
        $this->view->assign('help_url', $this->gen_help_url('menu_edit'));
        $this->processTemplate('pages/design/menu_form.tpl');
    }

    protected function _buildMenuTree($parent = '', $level = 0)
    {
        if (empty($this->menu_items[$parent])) {
            return [];
        }
        $lang_id = $this->language->getContentLanguageID();
        foreach ($this->menu_items[$parent] as $item) {
            $this->menu_tree[$item['item_id']] = [
                'item_id' => $item['item_id'],
                'text'    => str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $item['item_text'][$lang_id],
                'level'   => $level,
            ];
            $this->_buildMenuTree($item['item_id'], $level + 1);
        }
        return true;
    }

    protected function _validateForm($post)
    {
        if (!$this->user->canModify('design/menu')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!empty($post['item_id'])) {
            $ids = $this->menu->getItemIds();
            if (in_array($post['item_id'], $ids)) {
                $this->error['item_id'] = $this->language->get('error_non_unique');
            }
        }
        if (empty ($post['item_id']) && empty ($this->request->get['item_id'])) {
            $this->error['item_id'] = $this->language->get('error_empty');
        }

        if (empty ($post['item_text'][$this->language->getContentLanguageID()])) {
            $this->error['item_text'] = $this->language->get('error_empty');
        }
        if (empty ($post['item_url'])) {
            $this->error['item_url'] = $this->language->get('error_empty');
        }

        $this->extensions->hk_ValidateData($this, __FUNCTION__, $post);

        return (!$this->error);
    }
}
