<?php
/**
 * View an employee's timeclock history.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/manageclock') && !gatekeeper('com_hrm/viewclock') && !gatekeeper('com_hrm/viewownclock') )
	punt_user(null, pines_url('com_hrm', 'employee/timeclock/view', array('id' => $_REQUEST['id'])));

$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
if (!isset($employee->guid)) {
	pines_error('Requested employee id is not accessible.');
	return;
}

if (!gatekeeper('com_hrm/manageclock') && !gatekeeper('com_hrm/viewclock') && !$_SESSION['user']->is($employee)) {
	pines_notice('You only have the ability to view your own timeclock.');
	return;
}

$employee->timeclock->print_timeclock_view();

?>