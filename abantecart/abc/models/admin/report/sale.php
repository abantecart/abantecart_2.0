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

namespace abc\models\admin;

use abc\core\engine\Model;
use H;

class ModelReportSale extends Model
{
    /**
     * @param array $data
     * @param string $mode
     *
     * @return array|int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \abc\core\lib\AException
     */
    public function getSaleReport($data = [], $mode = 'default')
    {
        $filter = (isset($data['filter']) ? $data['filter'] : []);
        if (isset($filter['group'])) {
            $group = $filter['group'];
        } else {
            $group = $data['group'];
        }
        if (!H::has_value($group)) {
            $group = 'week';
        }

        if ($mode == 'total_only') {
            switch ($group) {
                case 'day';
                    $inc_sql = "COUNT(DISTINCT YEAR(date_added), MONTH(date_added), DAY(date_added))";
                    break;
                default:
                case 'week':
                    $inc_sql = "COUNT(DISTINCT YEAR(date_added), WEEK(date_added))";
                    break;
                case 'month':
                    $inc_sql = "COUNT(DISTINCT YEAR(date_added), MONTH(date_added))";
                    break;
                case 'year':
                    $inc_sql = "COUNT(DISTINCT YEAR(date_added))";
                    break;
            }
            $inc_sql .= " AS total ";
        } else {
            if ($mode == 'summary') {
                $inc_sql = 'COUNT(*) AS orders, 
						SUM(total) AS total_amount';
            } else {
                $inc_sql = "MIN(date_added) AS date_start, 
						MAX(date_added) AS date_end, 
						COUNT(*) AS orders, 
						SUM(total) AS total ";
            }
        }

        $sql = "SELECT ".$inc_sql." 
				FROM `".$this->db->table_name("orders")."`";

        if ($filter['order_status'] == 'confirmed') {
            $sql .= " WHERE order_status_id > 0 ";
        } elseif ((int)$filter['order_status']) {
            $sql .= " WHERE order_status_id = ".(int)$filter['order_status']." ";
        } else {
            //all orders
            $sql .= " WHERE order_status_id >= 0";
        }
        if (isset($filter['date_start'])) {
            $date_start =
                H::dateDisplay2ISO($filter['date_start'], $this->language->get('date_format_short'));
        } else {
            $date_start = date('Y-m-d', strtotime('-7 day'));
        }
        if (isset($filter['date_end'])) {
            $date_end = H::dateDisplay2ISO($filter['date_end'], $this->language->get('date_format_short'));
        } else {
            $date_end = date('Y-m-d', time());
        }
        $sql .= " AND (DATE_FORMAT(date_added,'%Y-%m-%d') >= DATE_FORMAT('".$this->db->escape($date_start)."','%Y-%m-%d') 
				  AND DATE_FORMAT(date_added,'%Y-%m-%d') <= DATE_FORMAT('".$this->db->escape($date_end)
            ."','%Y-%m-%d') )";

        //If for total, we done building the query
        if ($mode == 'total_only') {
            $query = $this->db->query($sql);
            return $query->row['total'];
        }
        if ($mode == 'summary') {
            $query = $this->db->query($sql);
            return $query->row;
        }

        switch ($group) {
            case 'day';
                $sql .= " GROUP BY DATE(date_added)";
                break;
            default:
            case 'week':
                $sql .= " GROUP BY WEEK(date_added)";
                break;
            case 'month':
                $sql .= " GROUP BY MONTH(date_added)";
                break;
            case 'year':
                $sql .= " GROUP BY YEAR(date_added)";
                break;
        }

        if (isset($data['sort'])) {
            $sql .= " ORDER BY ".$this->db->escape($data['sort'])." ".$this->db->escape($data['order']);
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT ".(int)$data['start'].",".(int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * @param array $data
     *
     * @return array|int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \abc\core\lib\AException
     */
    public function getSaleReportTotal($data = [])
    {
        return $this->getSaleReport($data, 'total_only');
    }

    /**
     * @param array $data
     *
     * @return array|int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \abc\core\lib\AException
     */
    public function getSaleReportSummary($data = [])
    {
        return $this->getSaleReport($data, 'summary');
    }

    /**
     * @param array $data
     * @param string $mode
     *
     * @return array|int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \abc\core\lib\AException
     */
    public function getTaxesReport($data = [], $mode = 'default')
    {
        $filter = (isset($data['filter']) ? $data['filter'] : []);
        if (isset($filter['group'])) {
            $group = $filter['group'];
        } else {
            $group = $data['group'];
        }
        if (!H::has_value($group)) {
            $group = 'week';
        }

        if ($mode == 'total_only') {
            switch ($group) {
                case 'day';
                    $inc_sql = "COUNT(DISTINCT YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added), ot.title)";
                    break;
                default:
                case 'week':
                    $inc_sql = "COUNT(DISTINCT YEAR(o.date_added), WEEK(o.date_added), ot.title)";
                    break;
                case 'month':
                    $inc_sql = "COUNT(DISTINCT YEAR(o.date_added), MONTH(o.date_added), ot.title)";
                    break;
                case 'year':
                    $inc_sql = "COUNT(DISTINCT YEAR(o.date_added), ot.title)";
                    break;
            }
            $inc_sql .= " AS total ";
        } else {
            if ($mode == 'summary') {
                $inc_sql = 'COUNT(*) AS orders, SUM(total) AS total_amount';
            } else {
                $inc_sql = "MIN(o.date_added) AS date_start, 
                            MAX(o.date_added) AS date_end, ot.title, 
                            SUM(ot.value) AS total, 
                            COUNT(o.order_id) AS orders ";
            }
        }

        $sql = "SELECT ".$inc_sql."
				FROM `".$this->db->table_name("orders")."` o 
				LEFT JOIN `".$this->db->table_name("order_totals")."` ot 
					ON (o.order_id = ot.order_id) 
				WHERE ot.type = 'tax' ";

        if (H::has_value($filter['order_status'])) {
            $sql .= " AND  o.order_status_id = ".(int)$filter['order_status']." ";
        }
        if (isset($filter['date_start'])) {
            $date_start =
                H::dateDisplay2ISO($filter['date_start'], $this->language->get('date_format_short'));
        } else {
            $date_start = date('Y-m-d', strtotime('-7 day'));
        }
        if (isset($filter['date_end'])) {
            $date_end = H::dateDisplay2ISO($filter['date_end'], $this->language->get('date_format_short'));
        } else {
            $date_end = date('Y-m-d', time());
        }
        $sql .= " AND (DATE_FORMAT(o.date_added,'%Y-%m-%d') >= DATE_FORMAT('".$this->db->escape($date_start)."','%Y-%m-%d') 
				  AND DATE_FORMAT(o.date_added,'%Y-%m-%d') <= DATE_FORMAT('".$this->db->escape($date_end)
            ."','%Y-%m-%d') )";

        if ($mode == 'total_only') {
            $query = $this->db->query($sql);
            return $query->row['total'];
        }
        if ($mode == 'summary') {
            $query = $this->db->query($sql);
            return $query->row;
        }

        switch ($group) {
            case 'day';
                $sql .= " GROUP BY DATE(o.date_added)";
                break;
            default:
            case 'week':
                $sql .= " GROUP BY WEEK(o.date_added)";
                break;
            case 'month':
                $sql .= " GROUP BY MONTH(o.date_added)";
                break;
            case 'year':
                $sql .= " GROUP BY YEAR(o.date_added)";
                break;
        }

        if (isset($data['sort'])) {
            $sql .= " ORDER BY ".$this->db->escape($data['sort'])." ".$this->db->escape($data['order']);
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT ".(int) $data['start'].",".(int) $data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * @param array $data
     *
     * @return array|int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \abc\core\lib\AException
     * @throws AException
     * @throws ReflectionException
     */
    public function getTaxesReportTotal($data = [])
    {
        return $this->getTaxesReport($data, 'total_only');
    }

    /**
     * @param array $data
     * @param string $mode
     *
     * @return array|int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \abc\core\lib\AException
     */
    public function getShippingReport($data = [], $mode = 'default')
    {
        $filter = (isset($data['filter']) ? $data['filter'] : []);
        if (isset($filter['group'])) {
            $group = $filter['group'];
        } else {
            $group = $data['group'];
        }
        if (!H::has_value($group)) {
            $group = 'week';
        }

        if ($mode == 'total_only') {
            switch ($group) {
                case 'day';
                    $inc_sql = "COUNT(DISTINCT YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added), ot.title)";
                    break;
                default:
                case 'week':
                    $inc_sql = "COUNT(DISTINCT YEAR(o.date_added), WEEK(o.date_added), ot.title)";
                    break;
                case 'month':
                    $inc_sql = "COUNT(DISTINCT YEAR(o.date_added), MONTH(o.date_added), ot.title)";
                    break;
                case 'year':
                    $inc_sql = "COUNT(DISTINCT YEAR(o.date_added), ot.title)";
                    break;
            }
            $inc_sql .= " AS total ";
        } else {
            if ($mode == 'summary') {
                $inc_sql = 'COUNT(*) AS orders, SUM(total) AS total_amount';
            } else {
                $inc_sql = "MIN(o.date_added) AS date_start, 
                            MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, 
                            COUNT(o.order_id) AS orders ";
            }
        }

        $sql = "SELECT ".$inc_sql." 
                FROM `".$this->db->table_name("orders")."` o 
                LEFT JOIN `".$this->db->table_name("order_totals")."` ot 
                    ON (o.order_id = ot.order_id) 
                WHERE ot.type = 'shipping' ";

        if (H::has_value($filter['order_status'])) {
            $sql .= " AND  o.order_status_id = ".(int) $filter['order_status']." ";
        }
        if (isset($filter['date_start'])) {
            $date_start = H::dateDisplay2ISO(
                $filter['date_start'],
                $this->language->get('date_format_short')
            );
        } else {
            $date_start = date('Y-m-d', strtotime('-7 day'));
        }
        if (isset($filter['date_end'])) {
            $date_end = H::dateDisplay2ISO($filter['date_end'], $this->language->get('date_format_short'));
        } else {
            $date_end = date('Y-m-d', time());
        }
        $sql .= " AND (DATE_FORMAT(o.date_added,'%Y-%m-%d') >= DATE_FORMAT('".$this->db->escape($date_start)."','%Y-%m-%d') 
                  AND DATE_FORMAT(o.date_added,'%Y-%m-%d') <= DATE_FORMAT('".$this->db->escape($date_end)."','%Y-%m-%d') )";

        if ($mode == 'total_only') {
            $query = $this->db->query($sql);
            return $query->row['total'];
        }
        if ($mode == 'summary') {
            $query = $this->db->query($sql);
            return $query->row;
        }

        switch ($group) {
            case 'day';
                $sql .= " GROUP BY DATE(o.date_added)";
                break;
            default:
            case 'week':
                $sql .= " GROUP BY WEEK(o.date_added)";
                break;
            case 'month':
                $sql .= " GROUP BY MONTH(o.date_added)";
                break;
            case 'year':
                $sql .= " GROUP BY YEAR(o.date_added)";
                break;
        }

        if (isset($data['sort'])) {
            $sql .= " ORDER BY ".$this->db->escape($data['sort'])." ".$this->db->escape($data['order']);
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT ".(int) $data['start'].",".(int) $data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * @param array $data
     *
     * @return array|int
     * @throws AException
     * @throws ReflectionException
     */
    public function getShippingReportTotal($data = [])
    {
        return $this->getShippingReport($data, 'total_only');
    }

    /**
     * @param array $data
     * @param string $mode
     *
     * @return array|int
     * @throws AException
     * @throws ReflectionException
     */
    public function getCouponsReport($data = [], $mode = 'default')
    {
        $filter = $data['filter'] ?? [];

        if ($mode == 'total_only') {
            $inc_sql = "COUNT(DISTINCT o.coupon_id) AS total ";
        } else {
            //condition if coupon is deleted
            $inc_sql = "IF(cd.name IS NULL OR cd.name = '', ot.title, cd.name) as coupon_name,
                        c.code, 
                        COUNT(DISTINCT o.order_id), 
                        SUM(o.total) AS total, 
                        SUM(ot.value) AS discount_total,  
                        COUNT(o.order_id) AS orders ";
        }

        $sql = "SELECT ".$inc_sql." 
                FROM `".$this->db->table_name("orders")."` o 
                LEFT JOIN `".$this->db->table_name("coupons")."` c 
                    ON (o.coupon_id = c.coupon_id) ";

        if ($mode == 'default') {
            $sql .= "LEFT JOIN `".$this->db->table_name("coupon_descriptions")."` cd
                        ON (c.coupon_id = cd.coupon_id 
                            AND cd.language_id=".(int) $this->language->getContentLanguageID().")";
        }

        $sql .= "LEFT JOIN `".$this->db->table_name("order_totals")."` ot 
                    ON (o.order_id = ot.order_id)
                WHERE ot.type = 'discount' ";

        if (isset($filter['date_start'])) {
            $date_start = H::dateDisplay2ISO($filter['date_start'], $this->language->get('date_format_short'));
        } else {
            $date_start = date('Y-m-d', strtotime('-7 day'));
        }
        if (isset($filter['date_end'])) {
            $date_end = H::dateDisplay2ISO($filter['date_end'], $this->language->get('date_format_short'));
        } else {
            $date_end = date('Y-m-d', time());
        }
        $sql .= " AND (DATE_FORMAT(o.date_added,'%Y-%m-%d') >= DATE_FORMAT('".$this->db->escape($date_start)."','%Y-%m-%d') 
                    AND DATE_FORMAT(o.date_added,'%Y-%m-%d') <= DATE_FORMAT('".$this->db->escape($date_end)."','%Y-%m-%d') )";

        if ($mode == 'total_only') {
            $query = $this->db->query($sql);
            return $query->row['total'];
        }

        $sql .= " GROUP BY o.coupon_id ";

        $sort_data = [
            'coupon_name'    => 'cd.name',
            'code'           => 'c.code',
            'orders'         => 'COUNT(o.order_id)',
            'total'          => 'SUM(o.total)',
            'discount_total' => 'SUM(ot.value)',
        ];

        if (isset($data['sort']) && array_key_exists($data['sort'], $sort_data)) {
            $sql .= " ORDER BY ".$sort_data[$data['sort']];
        } else {
            $sql .= " ORDER BY c.coupon_id";
        }
        $sql .= " ".$this->db->escape($data['order']);

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT ".(int) $data['start'].",".(int) $data['limit'];
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * @param array $data
     *
     * @return array|int
     * @throws AException
     * @throws ReflectionException
     */
    public function getCouponsReportTotal($data = [])
    {
        return $this->getCouponsReport($data, 'total_only');
    }

}
