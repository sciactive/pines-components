<?php
/**
 * com_customer_timer_login_tracker class.
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
 * The login tracker.
 *
 * Calling factory() will retrieve the login tracker entity.
 *
 * @package Pines
 * @subpackage com_customer_timer
 */
class com_customer_timer_login_tracker extends entity {
	/**
	 * Load the tracker.
	 *
	 * If multiple tracker entities are found, the first one is used, and all
	 * others are deleted.
	 */
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_customer_timer', 'logins');
		$this->ac = (object) array('other' => 2);
		$this->customers = array();
		global $config;
		$entities = $config->entity_manager->get_entities_by_tags('com_customer_timer', 'logins');
		if (empty($entities))
			return;
		if (count($entities) > 1) {
			for ($i = 1; $i < count($entities); $i++)
				$entities[$i]->delete();
		}
		$entity = $entities[0];
		$this->guid = $entity->guid;
		$this->parent = $entity->parent;
		$this->tags = $entity->tags;
		$this->entity_cache = array();
		$this->put_data($entity->get_data());
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $config;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$config->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Logs a customer into the timer.
	 *
	 * @param com_customer_customer $customer The customer.
	 * @return bool True on success, false on failure.
	 */
	function login(&$customer) {
		if (!isset($customer->com_customer_timer))
			$customer->com_customer_timer = (object) array();
		// Save the time the customer logged in.
		$customer->com_customer_timer->last_login = time();
		$customer->save();
		// Add the customer to the login tracker.
		$this->customers[] = $customer;
		$this->save();
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
		global $config;
		// Remove the customer from the login tracker.
		$found = false;
		foreach ($this->customers as $key => &$cur_customer) {
			if ($customer->is($cur_customer)) {
				unset($this->customers[$key]);
				$found = true;
			}
		}
		if (!$found)
			return true;
		$this->save();
		$session_info = $config->run_customer_timer->get_session_info($customer);
		// Take points off the customer's account.
		$customer->adjust_points(-1 * $session_info['points']);
		$customer->save();
		// Save a transaction.
		$tx = com_customer_timer_tx::factory('com_customer_timer', 'transaction', 'account_tx');
		$tx->customer = $customer;
		$tx->minutes = $session_info['minutes'];
		$tx->points = $session_info['points'];
		$tx->login_time = $customer->com_customer_timer->last_login;
		$tx->logout_time = time();
		$tx->save();
		display_notice("Goodbye, you have been logged out. This session was {$session_info['minutes']} minutes long, for {$session_info['points']} points.");
		return true;
	}
}

?>