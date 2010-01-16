<?php
/**
 * com_customer_timer class.
 *
 * @package Pines
 * @subpackage com_customer_timer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_customer_timer main class.
 *
 * Uses the points from com_customer to time customers, and notify when they are
 * out of points.
 *
 * @package Pines
 * @subpackage com_customer_timer
 */
class com_customer_timer extends component {
	/**
	 * Calculates information about a customer's session.
	 *
	 * @param entity $customer The customer.
	 * @return array An array of point and minute values the customer has used.
	 */
	function get_session_info(&$customer) {
		global $config;
		// Calculate how many minutes they've been logged in.
		$minutes = (int) round((time() - $customer->com_customer_timer->last_login) / 60);
		// And how many points that costs.
		$points = $minutes * (int) $config->com_customer_timer->ppm;
		return array('minutes' => $minutes, 'points' => $points);
	}

	/**
	 * Logs a customer in or out, depending on their current status.
	 *
	 * @param int $id The customer's GUID.
	 * @param string $password The customer's password.
	 * @return bool True on success, false on failure.
	 */
	function login_logout($id, $password) {
		global $config;
		if (!is_numeric($id)) {
			display_notice('Please provide a customer ID.');
			return false;
		}
		$id = (int) $id;
		$customer = com_customer_customer::factory($id);
		if (is_null($customer->guid)) {
			display_notice('Customer ID not found.');
			return false;
		}
		if ($customer->password != $password) {
			display_notice('Customer ID and password do not match.');
			return false;
		}
		$logins = com_customer_timer_login_tracker::factory();
		if ($customer->in_array($logins->customers))
			return $logins->logout($customer);
		if ($customer->login_disabled) {
			display_notice('Login has been disabled for your account.');
			return false;
		}
		if ($customer->points <= 0) {
			display_notice('Your account balance has reached zero.');
			if (!$config->com_customer_timer->debtlogin)
				return false;
		}
		return $logins->login($customer);
	}
}

?>