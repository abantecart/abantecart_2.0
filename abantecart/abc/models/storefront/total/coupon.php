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

namespace abc\models\storefront;

use abc\core\ABC;
use abc\core\lib\APromotion;
use abc\core\engine\Model;

/**
 * Class ModelTotalCoupon
 *
 * @package abc\models\storefront
 */
class ModelTotalCoupon extends Model
{
    /**
     * @param $total_data
     * @param $total
     * @param $taxes
     * @param $cust_data
     *
     * @throws \abc\core\lib\AException
     * @throws \ReflectionException
     */
    public function getTotal(&$total_data, &$total, &$taxes, &$cust_data)
    {
        if (isset($cust_data['coupon']) && $this->config->get('coupon_status')) {
            /**
             * @var APromotion $promotion
             */
            $promotion = ABC::getObjectByAlias('APromotion');
            $coupon = $promotion->getCouponData($cust_data['coupon']);


            if ($coupon) {
                $discount_total = 0;

                if (!$coupon['product']) {
                    $coupon_total = $this->cart->getSubTotal();
                } else {
                    $coupon_total = 0;

                    foreach ($this->cart->getProducts() as $product) {
                        if (in_array($product['product_id'], $coupon['product'])) {
                            $coupon_total += $product['total'];
                        }
                    }
                }

                if ($coupon['type'] == 'F') {
                    $coupon['discount'] = min($coupon['discount'], $coupon_total);
                }

                foreach ($this->cart->getProducts() as $product) {
                    $discount = 0;

                    if (!$coupon['product']) {
                        $status = true;
                    } else {
                        if (in_array($product['product_id'], $coupon['product'])) {
                            $status = true;
                        } else {
                            $status = false;
                        }
                    }

                    if ($status) {
                        if ($coupon['type'] == 'F') {
                            $discount = $coupon['discount'] * ($product['total'] / $coupon_total);
                        } elseif ($coupon['type'] == 'P') {
                            $discount = $product['total'] / 100 * $coupon['discount'];
                        }

                        if ($product['tax_class_id']) {
                            $taxes[$product['tax_class_id']]['total'] -= $discount;
                            $taxes[$product['tax_class_id']]['tax'] -= $this->tax->calcTotalTaxAmount(
                                    $product['total'],
                                    $product['tax_class_id'])
                                - $this->tax->calcTotalTaxAmount(
                                    ($product['total'] - $discount),
                                    $product['tax_class_id']
                                );
                        }
                    }

                    $discount_total += $discount;
                }
                $ship_data = $cust_data['shipping_method'];
                if ($coupon['shipping'] && isset($ship_data)) {
                    if (isset($ship_data['tax_class_id']) && $ship_data['tax_class_id']) {
                        $taxes[$ship_data['tax_class_id']]['total'] -= $ship_data['cost'];
                        $taxes[$ship_data['tax_class_id']]['tax'] -= $this->tax->calcTotalTaxAmount(
                            $ship_data['cost'],
                            $ship_data['tax_class_id']
                        );
                    }

                    $discount_total += $ship_data['cost'];
                }

                $total_data[] = [
                    'id'         => 'coupon',
                    'key'        => 'coupon',
                    'title'      => $coupon['name'] . ':',
                    'text'       => '-' . $this->currency->format($discount_total),
                    'value'      => -$discount_total,
                    'data'       => [
                        'coupon_details' => $coupon,
                    ],
                    'sort_order' => (int)$this->config->get('coupon_sort_order'),
                    'total_type' => $this->config->get('coupon_total_type'),
                ];

                $total -= $discount_total;
            }
        }
    }
}