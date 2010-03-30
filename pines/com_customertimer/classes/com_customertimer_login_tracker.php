<?php
/**
 * com_customertimer_login_tracker class.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The login tracker.
 *
 * Calling factory() will retrieve the login tracker entity.
 *
 * @package Pines
 * @subpackage com_customertimer
 */
class com_customertimer_login_tracker extends entity {
	/**
	 * Load the tracker.
	 *
	 * If multiple tracker entities are found, the first one is used, and all
	 * others are deleted.
	 */
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_customertimer', 'logins');
		$this->ac = (object) array('user' => 3, 'group' => 3, 'other' => 3);
		$this->customers = array();
		global $pines;
		$entities = $pines->entity_manager->get_entities(array('tags' => array('com_customertimer', 'logins')));
		if (empty($entities))
			return;
		if (($count = count($entities)) > 1) {
			for ($i = 1; $i < $count; $i++)
				$entities[$i]->delete();
		}
		$entity = $entities[0];
		$this->guid = $entity->guid;
		$this->tags = $entity->tags;
		$this->put_data($entity->get_data());
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Check if a customer is logged in to the login tracker.
	 *
	 * Note that if the current user does not have access to the customer's
	 * entity, a false negative may be returned.
	 *
	 * @param com_customer_customer $customer The customer to check.
	 * @return bool True or false.
	 */
	function logged_in($customer) {
		foreach ($this->customers as $cur_entry) {
			if ($customer->is($cur_entry['customer']))
				return true;
		}
		return false;
	}

	/**
	 * Logs a customer into the timer.
	 *
	 * @param com_customer_customer $customer The customer.
	 * @return bool True on success, false on failure.
	 */
	function login(&$customer, $station = null) {
		if (is_null($customer->guid))
			return false;
		if ($this->logged_in($customer))
			return false;
		if (!isset($customer->com_customertimer))
			$customer->com_customertimer = (object) array();
		// Save the time the customer logged in.
		$customer->com_customertimer->last_login = time();
		if (!$customer->save()) {
			display_notice("Customer {$customer->name} cannot be saved.");
			return false;
		}
		// Add the customer to the login tracker.
		$this->customers[] = array('customer' => $customer, 'station' => $station);
		if (!$this->save()) {
			display_notice('Time tracker could not be updated.');
			return false;
		}
		display_notice("Welcome {$customer->name}. You have been logged in.");
		return true;
	}

	/**
	 * Logs a customer out of the timer.
	 *
	 * This process creates a transaction entity.
	 *
	 * @param com_customer_customer $customer The customer.
	 * @return bool True on success, false on failure.
	 */
	function logout(&$customer) {
		global $pines;
		// Remove the customer from the login tracker.
		$found = false;
		foreach ($this->customers as $key => &$cur_entry) {
			if ($customer->is($cur_entry['customer'])) {
				$station = $cur_entry['station'];
				unset($this->customers[$key]);
				$found = true;
			}
		}
		if (!$found)
			return false;
		if (!$this->save()) {
			display_notice('Time tracker could not be updated.');
			return false;
		}
		$session_info = $pines->com_customertimer->get_session_info($customer);
		// Take points off the customer's account.
		$customer->adjust_points(-1 * $session_info['points']);
		if (!$customer->save()) {
			display_notice("Customer {$customer->name} cannot be saved. Their points could not be deducted.");
			return false;
		}
		// Save a transaction.
		$tx = com_customertimer_tx::factory('com_customertimer', 'transaction', 'account_tx');
		$tx->user = $_SESSION['user'];
		$tx->location = $_SESSION['user']->group;
		$tx->customer = $customer;
		$tx->station = $station;
		$tx->minutes = $session_info['minutes'];
		$tx->points = $session_info['points'];
		$tx->points_remain = $customer->points;
		$tx->login_time = $customer->com_customertimer->last_login;
		$tx->logout_time = time();
		$tx->save();
		display_notice("Goodbye, you have been logged out. This session was {$session_info['minutes']} minutes long, for {$session_info['points']} points.");
		return true;
	}

	/**
	 * Save the customer.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		$return = parent::save();
		if (isset($this->uid) || isset($this->gid)) {
			unset($this->uid);
			unset($this->gid);
			$return = parent::save();
		}
		return $return;
	}
}

?>