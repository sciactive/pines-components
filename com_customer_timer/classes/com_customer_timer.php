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
	function login_logout($id, $password) {
		global $config;
		if (!is_numeric($id)) {
			display_notice('Please provide a customer ID.');
			return false;
		}
		$id = (int) $id;
		$customer = $config->run_sales->get_customer($id);
		if (is_null($customer)) {
			display_notice('Customer ID not found.');
			return false;
		}
		if ($customer->com_customer->password != $password) {
			display_notice('Customer ID and password do not match.');
			return false;
		}
		$logins = $this->get_login_entity();
		if (in_array($customer, $logins->customers)) {
			return $this->logout($customer, $logins);
		}
		if ($customer->com_customer->login_disabled) {
			display_notice('Login has been disabled for your account.');
			return false;
		}
		if ($customer->com_customer->points <= 0) {
			display_notice('Your account balance has reached zero.');
			return false;
		}
		return $this->login($customer, $logins);
	}

	function login(&$customer, $logins) {
		if (!isset($customer->com_customer_timer))
			$customer->com_customer_timer = (object) array();
		// Save the time the customer logged in.
		$customer->com_customer_timer->last_login = time();
		$customer->save();
		// Add the customer to the login tracker.
		$logins->customers[] = $customer;
		$logins->save();
		display_notice("Welcome {$customer->name}. You have been logged in.");
		return true;
	}

	function logout(&$customer, $logins) {
		global $config;
		// Remove the customer from the login tracker.
		foreach ($logins->customers as $key => &$cur_customer) {
			if ($customer->is($cur_customer)) {
				unset($logins->customers[$key]);
			}
		}
		$logins->save();
		$session_info = $this->get_session_info($customer);
		// Take points off the customer's account.
		$config->run_customer->adjust_points($customer, -1 * $session_info['points']);
		$customer->save();
		// Save a transaction.
		$tx = new entity('com_customer_timer', 'transaction', 'account_tx');
		$tx->customer = $customer;
		$tx->minutes = $session_info['minutes'];
		$tx->points = $session_info['points'];
		$tx->login_time = $customer->com_customer_timer->last_login;
		$tx->logout_time = time();
		$tx->save();
		display_notice("Goodbye, you have been logged out. This session was {$session_info['minutes']} minutes long, for {$session_info['points']} points.");
		return true;
	}
	
	function get_session_info(&$customer) {
		global $config;
		// Calculate how many minutes they've been logged in.
		$minutes = (int) floor((time() - $customer->com_customer_timer->last_login) / 60);
		// And how many points that costs.
		$points = $minutes * (int) $config->com_customer_timer->ppm;
		return array('minutes' => $minutes, 'points' => $points);
	}

	function get_login_entity() {
		global $config;
		$entities = $config->entity_manager->get_entities_by_tags('com_customer_timer', 'logins');
		if (empty($entities)) {
			$return = new entity('com_customer_timer', 'logins');
			$return->ac = (object) array('other' => 2);
			$return->customers = array();
		} else {
			if (count($entities) > 1) {
				for ($i = 1; $i < count($entities); $i++) {
					$entities[$i]->delete();
				}
			}
			$return = $entities[0];
		}
		return $return;
	}
}

?>