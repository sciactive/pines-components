<?php
/**
 * View an employee's timeclock history.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/manageclock') && !gatekeeper('com_hrm/viewclock') && !gatekeeper('com_hrm/viewownclock') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'viewtimeclock', array('id' => $_REQUEST['id']), false));

$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
if (is_null($employee->guid)) {
	display_error('Requested employee id is not accessible.');
	return;
}

if (!gatekeeper('com_hrm/manageclock') && !gatekeeper('com_hrm/viewclock')) {
	if (!$_SESSION['user']->is($employee->user_account)) {
		display_notice('You only have the ability to view your own timeclock.');
		return;
	}
}

if ( empty($employee->timeclock) )
	display_notice("No timeclock data is stored for employee [{$employee->name}].");

$employee->print_timeclock_view();

?>