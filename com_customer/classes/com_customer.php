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
	 * Display a sidebar with a link to edit a customer account.
	 *
	 * When a customer is edited in com_sales, this is called and provides a
	 * sidebar with info about the customer's account, and a link to open it.
	 *
	 * @param array $array The arguments array.
	 * @param string $hook The name of the hook.
	 * @param mixed $object The object on with the hook was called.
	 * @return array The arguments array.
	 */
	function hook_customer_view($array, $hook, $object) {
		global $config;
		if ( gatekeeper('com_customer/editcustomer') && isset($object->guid) ) {
			$customer = com_sales_customer::factory($object->guid);
			if (isset($customer->guid)) {
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
	 * Add points to a customer for a product sale.
	 *
	 * @param array $array The details array.
	 */
	function product_action_add_points($array) {
		global $config;
		foreach(explode(',', $config->com_customer->pointvalues) as $cur_value) {
			if (!is_numeric($cur_value))
				continue;
			$cur_value = intval($cur_value);
			if ($array['name'] == "com_customer/add_points_$cur_value") {
				$this->adjust_points($array['sale']->customer, $cur_value);
				$array['sale']->customer->save();
			}
		}
	}
}

?>