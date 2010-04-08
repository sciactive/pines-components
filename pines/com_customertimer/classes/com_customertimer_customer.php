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
		if ($this->com_customertimer_is_logged_in($floor, $station))
			return true;
		
	}

	public function com_customertimer_logout(&$floor, $station) {
		if (!$this->com_customertimer_is_logged_in($floor, $station))
			return true;
	}

	public function com_customertimer_is_logged_in(&$floor, $station = null) {
		//
	}
}

?>