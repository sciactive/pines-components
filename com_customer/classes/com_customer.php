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
 * Provides a CRM.
 *
 * @package Pines
 * @subpackage com_customer
 */
class com_customer extends component {
	/**
	 * Whether to integrate with com_sales.
	 * 
	 * @var bool $com_sales
	 */
	var $com_sales;

	/**
	 * Check whether com_sales is installed and we should integrate with it.
	 *
	 * Places the result in $this->com_sales.
	 */
	function __construct() {
		global $config;
		$this->com_sales = $config->depend->check('component', 'com_sales');
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

		$module->customers = $config->entity_manager->get_entities(array('tags' => array('com_customer', 'customer'), 'class' => com_customer_customer));

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
				$array['sale']->customer->adjust_points($cur_value);
				$array['sale']->customer->save();
			}
		}
	}
}

?>