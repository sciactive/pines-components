<?php
/**
 * com_customertimer_customer class.
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
 * A customer with additional timer functions.
 *
 * @package Pines
 * @subpackage com_customertimer
 */
class com_customertimer_customer extends com_customer_customer {
	public function com_customertimer_login(&$floor, $station) {
		// Todo: Write something to the customer so we know we have write access.
		if ($this->com_customertimer_is_logged_in($floor, $station)) {
			pines_notice('This customer is already logged in here.');
			return true;
		}
		if (isset($floor->active_stations[$station])) {
			pines_error('There is already a customer logged in to this station.');
			return false;
		}
		$floor->active_stations[$station] = array(
			'customer' => $this,
			'time_in' => time(),
			'logged_in_by' => $_SESSION['user']
		);
	}

	public function com_customertimer_logout(&$floor, $station) {
		if (!$this->com_customertimer_is_logged_in($floor, $station)) {
			pines_notice('This customer is not logged in here.');
			return true;
		}
		// Todo: Subtract points, make transaction.
		unset($floor->active_stations[$station]);
	}

	public function com_customertimer_is_logged_in(&$floor, $station = null) {
		if (isset($station))
			return (isset($floor->active_stations[$station]) && $this->is($floor->active_stations[$station]['customer']));
		foreach ($floor->active_stations as $cur_station) {
			if (!isset($station) && $this->is($cur_station['customer']))
				return true;
		}
		return false;
	}
}

?>