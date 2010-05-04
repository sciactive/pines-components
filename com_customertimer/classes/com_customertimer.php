<?php
/**
 * com_customertimer class.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_customertimer main class.
 *
 * Uses the points from com_customer to time customers, and notify when they are
 * out of points.
 *
 * @package Pines
 * @subpackage com_customertimer
 */
class com_customertimer extends component {
	/**
	 * Creates and attaches a module which lists floors.
	 */
	public function list_floors() {
		global $pines;

		$module = new module('com_customertimer', 'list_floors', 'content');

		$module->floors = $pines->entity_manager->get_entities(array('tags' => array('com_customertimer', 'floor'), 'class' => com_customertimer_floor));

		if ( empty($module->floors) )
			pines_notice('There are no floors.');
	}

	/* Keeping this to use some code later.
	public function login_logout($id, $password) {
		global $pines;
		if (!is_numeric($id)) {
			pines_notice('Please provide a customer ID.');
			return false;
		}
		$id = (int) $id;
		$customer = com_customer_customer::factory($id);
		if (!isset($customer->guid)) {
			pines_notice('Customer ID not found.');
			return false;
		}
		if ($customer->password != $password) {
			pines_notice('Customer ID and password do not match.');
			return false;
		}
		$logins = com_customertimer_login_tracker::factory();
		foreach ($logins->customers as $cur_entry) {
			if ($customer->is($cur_entry['customer']))
				return $logins->logout($customer);
		}
		if ($customer->login_disabled) {
			pines_notice('Login has been disabled for your account.');
			return false;
		}
		if ($customer->points <= 0) {
			pines_notice('Your account balance has reached zero.');
			if (!$pines->config->com_customertimer->debt_login)
				return false;
		}
		return $logins->login($customer);
	}
	 */
}

?>