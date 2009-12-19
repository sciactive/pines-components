<?php
/**
 * com_customer class.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_customer main class.
 *
 * Extends com_sales' native customer management, providing several enhanced
 * features.
 *
 * @package Pines
 * @subpackage com_customer
 */
class com_customer extends component {
	/**
	 * Add or subtract points from a customer's account.
	 *
	 * @param entity $customer The customer entity.
	 * @param int $point_adjust The positive or negative point value to add.
	 */
	function adjust_points(&$customer, $point_adjust) {
		if (!is_a($customer, 'entity'))
			return;
		if (!isset($customer->com_customer))
			$customer->com_customer = (object) array();
		$point_adjust = (int) $point_adjust;
		// Check that there is a point value.
		if (!is_int($customer->com_customer->points))
			$customer->com_customer->points = 0;
		// Check the total value.
		if (!is_int($customer->com_customer->total_points))
			$customer->com_customer->total_points = $customer->com_customer->points;
		// Check the peak value.
		if (!is_int($customer->com_customer->peak_points))
			$customer->com_customer->peak_points = $customer->com_customer->points;
		// Do the adjustment.
		if ($point_adjust != 0) {
			if ($point_adjust > 0)
				$customer->com_customer->total_points += $point_adjust;
			$customer->com_customer->points += $point_adjust;
			if ($customer->com_customer->points > $customer->com_customer->peak_points)
				$customer->com_customer->peak_points = $customer->com_customer->points;
		}
	}

	function hook_customer_view($array) {
		global $config;
		if ( gatekeeper('com_customer/editcustomer') && is_numeric($array[2]) ) {
			$customer = $config->run_sales->get_customer((int) $array[2]);
			if (!is_null($customer)) {
				$module = new module('com_customer', 'sidebar_customer', 'right');
				$module->entity = $customer;
			}
		}
		return $array;
	}

	/**
	 * Creates and attaches a module which lists customers.
	 */
	function list_customers() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_customer', 'list_customers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customer/list_customers'];

		$module->customers = $config->entity_manager->get_entities_by_tags('com_sales', 'customer');

		if ( empty($module->customers) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no customers.");
		}
	}

	/**
	 * Creates and attaches a module containing a form for editing a
	 * customer.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the customer to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_customer_form($new_option, $new_action, $id = NULL) {
		global $config;
		$config->editor->load();
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_customer', 'form_customer', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $config->run_sales->get_customer($id);
			if (is_null($module->entity)) {
				display_error('Requested customer id is not accessible.');
				$module->detach();
				return;
			}
		}
		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Add points to a customer for a product sale.
	 *
	 * @param array $array The details array.
	 */
	function product_action_add_points($array) {
		switch ($array['name']) {
			case 'com_customer/add_points_1':
				$this->adjust_points($array['sale']->customer, 1);
				break;
			case 'com_customer/add_points_5':
				$this->adjust_points($array['sale']->customer, 5);
				break;
			case 'com_customer/add_points_10':
				$this->adjust_points($array['sale']->customer, 10);
				break;
			case 'com_customer/add_points_50':
				$this->adjust_points($array['sale']->customer, 50);
				break;
			case 'com_customer/add_points_60':
				$this->adjust_points($array['sale']->customer, 60);
				break;
			case 'com_customer/add_points_100':
				$this->adjust_points($array['sale']->customer, 100);
				break;
			case 'com_customer/add_points_120':
				$this->adjust_points($array['sale']->customer, 120);
				break;
			case 'com_customer/add_points_250':
				$this->adjust_points($array['sale']->customer, 250);
				break;
			case 'com_customer/add_points_500':
				$this->adjust_points($array['sale']->customer, 500);
				break;
			case 'com_customer/add_points_1000':
				$this->adjust_points($array['sale']->customer, 1000);
				break;
		}
		$array['sale']->customer->save();
	}
}

?>