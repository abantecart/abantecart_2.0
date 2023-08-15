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

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\Registry;
use abc\core\engine\AResource;
use abc\models\catalog\Product;
use abc\models\QueryBuilder;
use abc\modules\traits\ProductListingTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ControllerPagesProductSpecial
 *
 * @package abc\controllers\storefront
 */
class ControllerPagesProductBestSeller extends AController
{
    use ProductListingTrait;

    public function __construct(Registry $registry, $instance_id, $controller, $parent_controller = '')
    {
        parent::__construct($registry, $instance_id, $controller, $parent_controller);
        $this->fillSortsList();
    }

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $request = $this->request->get;

        $cart_rt = $this->config->get('embed_mode') ? 'r/checkout/cart/embed' : 'checkout/cart';

        $this->loadLanguage('product/bestseller');
        $this->document->setTitle($this->language->get('heading_title'));

        if ($this->config->get('config_require_customer_login') && !$this->customer->isLogged()) {
            abc_redirect($this->html->getSecureURL('account/login'));
        }

        $page = $request['page'] ?? 1;

        $sorting_href = $request['sort'];
        if (!$sorting_href || !isset($this->data['sorts'][$request['sort']])) {
            $sorting_href = $this->config->get('config_product_default_sort_order');
        }

        list($sort, $order) = explode("-", $sorting_href);

        $limit = $this->config->get('config_catalog_limit');
        if (isset($request['limit']) && intval($request['limit']) > 0) {
            $limit = intval($request['limit']);
            if ($limit > 50) {
                $limit = 50;
            }
        }

        $this->data['search_parameters'] =
            [
                'sort'                => $sort,
                'order'               => $order,
                'start'               => ($page - 1) * $limit,
                'limit'               => $limit,
            ];

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->resetBreadcrumbs();
        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getHomeURL(),
                'text'      => $this->language->get('text_home'),
                'separator' => false,
            ]
        );

        $url = '';
        if (isset($request['sort'])) {
            $url .= '&sort=' . $request['sort'];
        }

        if (isset($request['order'])) {
            $url .= '&order=' . $request['order'];
        }

        if (isset($request['page'])) {
            $url .= '&page=' . $request['page'];
        }
        if (isset($request['limit'])) {
            $url .= '&limit=' . $request['limit'];
        }

        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getNonSecureURL('product/bestseller', $url),
                'text'      => $this->language->get('heading_title'),
                'separator' => $this->language->get('text_separator'),
            ]
        );

        /** @see Product::getProducts() */
        $productsList = Product::getBestSellerProducts($this->data['search_parameters']);
        /** @see QueryBuilder::get() */
        $product_total = $productsList::getFoundRowsCount();

        if ($product_total) {
            $this->loadModel('tool/seo_url');
            $this->data['button_add_to_cart'] = $this->language->get('button_add_to_cart');
            $product_ids = $productsList?->pluck('product_id')->toArray();

            //Format product data specific for confirmation page
            $resource = new AResource('image');
            $thumbnails = $resource->getMainThumbList(
                'products',
                $product_ids,
                $this->config->get('config_image_product_width'),
                $this->config->get('config_image_product_height')
            );

            $this->data['is_customer'] = false;
            $wishlist = [];
            if ($this->customer->isLogged() || $this->customer->isUnauthCustomer()) {
                $this->data['is_customer'] = true;
                $wishlist = $this->customer->getWishList();
            }

            $product_ids = $productsList->pluck('product_id')->toArray();

            //Format product data specific for confirmation page
            $resource = new AResource('image');
            $thumbnails = $resource->getMainThumbList(
                'products',
                $product_ids,
                $this->config->get('config_image_product_width'),
                $this->config->get('config_image_product_height')
            );

            //if single result, redirect to the product
            if (count($productsList) == 1) {
                abc_redirect(
                    $this->html->getSEOURL(
                        'product/product',
                        '&product_id=' . $productsList->first()->product_id,
                        '&encode')
                );
            }
            $products = [];
            /** @var Collection $listItem */
            foreach ($productsList as $i => $listItem) {
                $products[$i] = $listItem->toArray();
                $thumbnail = $thumbnails[$listItem['product_id']];
                $rating = $this->config->get('enable_reviews') ? $listItem['rating'] : false;
                $special = false;
                $discount = $listItem['discount_price'];

                $in_wishlist = false;
                if ($wishlist && $wishlist[$listItem['product_id']]) {
                    $in_wishlist = true;
                }

                if ($discount) {
                    $price = $this->currency->format(
                        $this->tax->calculate(
                            $discount,
                            $listItem['tax_class_id'],
                            $this->config->get('config_tax')
                        )
                    );
                } else {
                    $price = $this->currency->format(
                        $this->tax->calculate(
                            $listItem['price'],
                            $listItem['tax_class_id'],
                            $this->config->get('config_tax')
                        )
                    );
                    $special = $listItem['special_price'];
                    if ($special) {
                        $special =
                            $this->currency->format(
                                $this->tax->calculate(
                                    $special,
                                    $listItem['tax_class_id'],
                                    $this->config->get('config_tax')
                                )
                            );
                    }
                }

                $hasOptions = $listItem['option_count'];
                if ($hasOptions) {
                    $addToCartUrl = $this->html->getSEOURL(
                        'product/product',
                        '&product_id=' . $listItem['product_id'],
                        '&encode'
                    );
                } else {
                    if ($this->config->get('config_cart_ajax')) {
                        $addToCartUrl = '#';
                    } else {
                        $addToCartUrl = $this->html->getSecureURL(
                            $cart_rt,
                            '&product_id=' . $listItem['product_id'],
                            '&encode'
                        );
                    }
                }

                //check for stock status, availability and config
                $track_stock = false;
                $in_stock = false;
                $no_stock_text = $this->language->get('text_out_of_stock');
                $stock_checkout = $listItem['stock_checkout'] === ''
                    ? $this->config->get('config_stock_checkout')
                    : $listItem['stock_checkout'];
                $total_quantity = 0;
                if ($listItem['subtract']) {
                    $track_stock = true;
                    $total_quantity = $listItem['quantity'];
                    //we have stock or out of stock checkout is allowed
                    if ($total_quantity > 0 || $stock_checkout) {
                        $in_stock = true;
                    }
                }

                $products[$i]['rating'] = $rating;
                $products[$i]['stars'] = sprintf($this->language->get('text_stars'), $rating);
                $products[$i]['thumb'] = $thumbnail;
                $products[$i]['price'] = $price;
                $products[$i]['raw_price'] = $listItem['price'];
                $products[$i]['options'] = $hasOptions;
                $products[$i]['special'] = $special;
                $products[$i]['href'] = $this->html->getSEOURL(
                    'product/product',
                    '&keyword=' . $request['keyword']
                    . $url
                    . '&product_id=' . $listItem['product_id'],
                    '&encode');
                $products[$i]['add'] = $addToCartUrl;
                $products[$i]['description'] = html_entity_decode(
                    $listItem['description'],
                    ENT_QUOTES,
                    ABC::env('APP_CHARSET')
                );
                $products[$i]['track_stock'] = $track_stock;
                $products[$i]['in_stock'] = $in_stock;
                $products[$i]['no_stock_text'] = $no_stock_text;
                $products[$i]['total_quantity'] = $total_quantity;
                $products[$i]['in_wishlist'] = $in_wishlist;
                $products[$i]['product_wishlist_add_url'] = $this->html->getURL(
                    'product/wishlist/add',
                    '&product_id=' . $listItem['product_id']);
                $products[$i]['product_wishlist_remove_url'] = $this->html->getURL(
                    'product/wishlist/remove',
                    '&product_id=' . $listItem['product_id']);
            }
            $this->data['products'] = $products;

            if ($this->config->get('config_customer_price')) {
                $display_price = true;
            } elseif ($this->customer->isLogged()) {
                $display_price = true;
            } else {
                $display_price = false;
            }
            $this->data['display_price'] = $display_price;

            $url = '';
            if (isset($request['keyword'])) {
                $url .= '&keyword=' . $request['keyword'];
            }

            if (isset($request['category_id'])) {
                $url .= '&category_id=' . $request['category_id'];
            }

            if (isset($request['description'])) {
                $url .= '&description=' . $request['description'];
            }

            if (isset($request['model'])) {
                $url .= '&model=' . $request['model'];
            }

            if (isset($request['page'])) {
                $url .= '&page=' . $request['page'];
            }
            if (isset($request['limit'])) {
                $url .= '&limit=' . $request['limit'];
            }

            $sort_options = [];

            foreach ($this->data['sorts'] as $value => &$text) {
                $sort_options[$value] = $text;
                list($s, $o) = explode('-', $value);
                $text = [
                    'text'  => $text,
                    'value' => $value,
                    'href'  => $this->html->getURL('product/search', $url . '&sort=' . $s . '&order=' . $o, '&encode'),
                ];
            }

            $sorting = $this->html->buildElement(
                [
                    'type'    => 'selectbox',
                    'name'    => 'sort',
                    'options' => $sort_options,
                    'value'   => $sort . '-' . $order,
                ]
            );

            $this->data['sorting'] = $sorting;
            $url = '';
            if (isset($request['keyword'])) {
                $url .= '&keyword=' . $request['keyword'];
            }
            if (isset($request['category_id'])) {
                $url .= '&category_id=' . $request['category_id'];
            }

            if (isset($request['description'])) {
                $url .= '&description=' . $request['description'];
            }

            if (isset($request['model'])) {
                $url .= '&model=' . $request['model'];
            }

            if (isset($request['sort'])) {
                $url .= '&sort=' . $request['sort'];
            }

            $url .= '&sort=' . $sorting_href;
            $url .= '&limit=' . $limit;

            $this->data['pagination_bootstrap'] = $this->html->buildElement([
                'type'       => 'Pagination',
                'name'       => 'pagination',
                'text'       => $this->language->get('text_pagination'),
                'text_limit' => $this->language->get('text_per_page'),
                'total'      => $product_total,
                'page'       => $page,
                'limit'      => $limit,
                'url'        => $this->html->getURL('product/bestseller', $url . '&page={page}', '&encode'),
                'style'      => 'pagination',
            ]);
            $this->data['sort'] = $sort;
            $this->data['order'] = $order;
            $this->data['limit'] = $limit;

            $this->data['url'] = $this->html->getURL('product/bestseller');

            $this->data['review_status'] = $this->config->get('enable_reviews');
            $this->view->setTemplate('pages/product/bestseller.tpl');
        } else {
            $this->data['text_error'] = $this->language->get('text_empty');
            $continue = $this->html->buildElement(
                [
                    'type'  => 'button',
                    'name'  => 'continue_button',
                    'text'  => $this->language->get('button_continue'),
                    'style' => 'button',
                ]);
            $this->data['button_continue'] = $continue;
            $this->data['continue'] = $this->html->getHomeURL();
            $this->view->setTemplate('pages/error/not_found.tpl');
        }
        $this->view->batchAssign($this->data);
        $this->processTemplate();

        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}