<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2017 Belavier Commerce LLC

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

if (!class_exists('abc\core\ABC') || !\abc\core\ABC::env('IS_ADMIN')) {
	header('Location: static_pages/?forbidden='.basename(__FILE__));
}
class ModelSaleCustomerGroup extends Model {
	/**
	 * @param array $data
	 * @return int
	 */
	public function addCustomerGroup($data) {
		$this->db->query("INSERT INTO " . $this->db->table_name("customer_groups") . " 
							SET name = '" . $this->db->escape($data['name']) . "',
								tax_exempt = '" . $this->db->escape($data['tax_exempt']) . "'
						");
		return $this->db->getLastId();
	}

	/**
	 * @param int $customer_group_id
	 * @param array $data
	 */
	public function editCustomerGroup($customer_group_id, $data) {
		if ( !empty($data['name']) ) {
			$this->db->query(
					"UPDATE " . $this->db->table_name("customer_groups") . " 
					SET 
						name = '" . $this->db->escape($data['name']) . "'
					WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		}
		if ( isset($data['tax_exempt']) ) {
			$this->db->query(
					"UPDATE " . $this->db->table_name("customer_groups") . " 
					SET 
						tax_exempt = '" . $this->db->escape($data['tax_exempt']) . "'
					WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		}
	}

	/**
	 * @param int $customer_group_id
	 */
	public function deleteCustomerGroup($customer_group_id) {
		$this->db->query("DELETE FROM " . $this->db->table_name("customer_groups") . " 
						WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		$this->db->query("DELETE FROM " . $this->db->table_name("product_discounts") . " 
						WHERE customer_group_id = '" . (int)$customer_group_id . "'");
	}

	/**
	 * @param int $customer_group_id
	 * @return mixed
	 */
	public function getCustomerGroup($customer_group_id) {
		$query = $this->db->query(
				"SELECT DISTINCT * 
				FROM " . $this->db->table_name("customer_groups") . " 
				WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		return $query->row;
	}

	/**
	 * @param array $data
	 * @param string $mode
	 * @return mixed
	 */
	public function getCustomerGroups($data = array(), $mode = 'default') {
		if ($mode == 'total_only') {
			$sql = "SELECT count(*) as total 
					FROM " . $this->db->table_name("customer_groups") . " ";
		} else {
			$sql = "SELECT * 
					FROM " . $this->db->table_name("customer_groups") . " ";
		}

		if ( !empty($data['subsql_filter']) ) {
			$sql .= " WHERE ".$data['subsql_filter'];
		}

		//If for total, we done building the query
		if ($mode == 'total_only') {
		    $query = $this->db->query($sql);
		    return $query->row['total'];
		}
		
		$sql .= " ORDER BY name";
		if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
			
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
	
	public function getTotalCustomerGroups($data = array()) {
		return $this->getCustomerGroups($data, 'total_only');
	}
}
