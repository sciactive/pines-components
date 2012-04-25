<?php
/**
 * com_customertimer_customer class.
 *
 * @package Components\customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A customer with additional timer functions.
 *
 * @package Components\customertimer
 */
class com_customertimer_customer extends com_customer_customer {
	/**
	 * Create a new instance.
	 *
	 * @param int|string $id The ID or username of the customer to load, 0 for a new customer.
	 * @return com_customertimer_customer The new instance.
	 */
	public static function factory($id = 0) {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Calculate information about a customer's session.
	 *
	 * @param com_customertimer_floor &$floor The floor to use.
	 * @param string $station The station on the floor.
	 * @return array An array of point and minute values the customer has used.
	 */
	public function com_customertimer_get_session_info(&$floor, $station) {
		global $pines;
		// Make sure the customer is actually logged in.
		if (!$this->com_customertimer_is_logged_in($floor, $station)) {
			pines_notice('This customer is not logged in here.');
			return true;
		}

		// Calculate how many minutes they've been logged in.
		$minutes = (int) round((time() - $floor->active_stations[$station]['time_in']) / 60);

		// And how many points that costs.
		$ppm = (int) $pines->config->com_customertimer->ppm;
		$points = $minutes * $ppm;

		// Get any additional minutes the customer is using.
		// Todo: Check other floors to see if the customer is logged in there.
		$other_minutes = 0;
		foreach ($floor->active_stations as $cur_station => $cur_entry) {
			if ($cur_station == $station || !$this->is($cur_entry['customer']))
				continue;
			$other_minutes += time() - $cur_entry['time_in'];
		}
		$other_minutes = (int) round($other_minutes / 60);
		$other_points = $other_minutes * $ppm;

		// Check how many points the customer has left in their account.
		$points_remain = $this->points - ($points + $other_points);
		// If negatives aren't allowed, change it to 0.
		if ($points_remain < 0 && !$pines->config->com_customer->negpoints)
			$points_remain = 0;

		return array('minutes' => $minutes, 'points' => $points, 'other_minutes' => $other_minutes, 'other_points' => $other_points, 'points_remain' => $points_remain);
	}

	/**
	 * Check if a customer is logged into a given location.
	 *
	 * @param com_customertimer_floor|null &$floor The floor to check, or null to check all floors.
	 * @param string|null $station The station on the floor, or null to check all stations. Requires a floor.
	 * @return bool
	 */
	public function com_customertimer_is_logged_in(&$floor = null, $station = null) {
		if (isset($floor)) {
			// If a station is requested, check that the customer is in that station.
			if (isset($station))
				return (isset($floor->active_stations[$station]) && $this->is($floor->active_stations[$station]['customer']));
			// If no station is requested, look through each one.
			foreach ($floor->active_stations as $cur_station) {
				if (!isset($station) && $this->is($cur_station['customer']))
					return true;
			}
			// The customer is not logged in on this floor.
			return false;
		} else {
			global $pines;
			$get_floor = $pines->entity_manager->get_entity(
					array('class' => com_customertimer_floor, 'skip_ac' => true),
					array('&',
						'tag' => array('com_customertimer', 'floor'),
						'ref' => array('active_stations', $this)
					)
				);
			return (isset($get_floor));
		}
	}

	/**
	 * Log the customer into a station on a floor.
	 *
	 * @param com_customertimer_floor &$floor The floor to log the customer into.
	 * @param string $station The station on the floor.
	 * @return bool True on success, false on failure.
	 * @todo Password and login disabling support.
	 */
	public function com_customertimer_login(&$floor, $station) {
		// Make sure the customer is not already logged in.
		if ($this->com_customertimer_is_logged_in($floor, $station)) {
			pines_notice('This customer is already logged in here.');
			return true;
		}

		// Make sure we have write access to the customer.
		if (!$this->save()) {
			pines_error('Customer cannot be saved. Do you have permission?');
			return false;
		}

		// Make sure the station is available.
		if (isset($floor->active_stations[$station])) {
			pines_error('There is already a customer logged in to this station.');
			return false;
		}

		// Place the customer in the station.
		$floor->active_stations[$station] = array(
			'customer' => $this,
			'time_in' => time(),
			'points_in' => $this->points,
			'user' => $_SESSION['user']
		);

		// Save the floor.
		if (!$floor->save()) {
			pines_error('Floor entry cannot be saved. Do you have permission?');
			return false;
		}
		return true;
	}

	/**
	 * Log the customer out of a station on a floor.
	 *
	 * @param com_customertimer_floor &$floor The floor to log the customer out of.
	 * @param string $station The station on the floor.
	 * @return bool True on success, false on failure.
	 */
	public function com_customertimer_logout(&$floor, $station) {
		// Make sure the customer is actually logged in.
		if (!$this->com_customertimer_is_logged_in($floor, $station)) {
			pines_notice('This customer is not logged in here.');
			return true;
		}

		// Make sure we have write access to the floor.
		if (!$floor->save()) {
			pines_notice('Floor entry cannot be saved. Do you have permission?');
			return false;
		}

		// Take points off the customer's account.
		$session_info = $this->com_customertimer_get_session_info($floor, $station);
		$this->adjust_points(-1 * $session_info['points']);
		if (!$this->save()) {
			pines_notice("Customer {$this->name} cannot be saved. Their points have not been deducted. Do you have permission?");
			return false;
		}

		// Save a transaction.
		$tx = com_customertimer_tx::factory('com_customertimer', 'transaction', 'account_tx');
		$tx->location = $_SESSION['user']->group;
		$tx->floor = $floor;
		$tx->customer = $this;
		$tx->station = $station;
		$tx->minutes = $session_info['minutes'];
		$tx->points = $session_info['points'];
		$tx->points_in = $floor->active_stations[$station]['points_in'];
		$tx->points_remain = $session_info['points_remain'];
		$tx->login_time = $floor->active_stations[$station]['time_in'];
		$tx->logout_time = time();
		$tx->save();

		// Take the customer out of the floor.
		unset($floor->active_stations[$station]);
		if (!$floor->save()) {
			pines_error('Floor entry cannot be saved. Do you have permission?');
			return false;
		}
		pines_notice("Goodbye, you have been logged out. This session was {$session_info['minutes']} minutes long, for {$session_info['points']} points.");
		return true;
	}
}

?>