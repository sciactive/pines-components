<?php
/**
 * Load the clockin module.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_SESSION['user']) && $_SESSION['user']->employee && gatekeeper('com_hrm/clock')) {
	$employee = com_hrm_employee::factory($_SESSION['user']->guid);
	$employee->print_clockin();
	unset($employee);
}

?>