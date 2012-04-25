<?php
/**
 * Run an hours clocked report.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/viewclock') && !gatekeeper('com_hrm/viewownclock') )
	punt_user(null, pines_url('com_hrm', 'employee/timeclock/view'));

if (empty($_REQUEST['date_start']) || empty($_REQUEST['date_end'])) {
	pines_notice('Please provide a start and end date.');
	pines_redirect(pines_url('com_hrm', 'employee/timeclock/view'));
	return;
}

$module = new module('com_hrm', 'employee/timeclock/report', 'content');
$module->local_timezones = ($_REQUEST['local_timezones'] == 'ON');
$module->paginate = ($_REQUEST['paginate'] == 'ON');
$module->show_details = ($_REQUEST['show_details'] == 'ON');

if (!$module->local_timezones) {
	$timezone = date_default_timezone_get();
} else {
	$cur_timezone = date_default_timezone_get();
}
$date_start = $module->date_start = strtotime($_REQUEST['date_start']);
$date_end = $module->date_end = strtotime($_REQUEST['date_end'].' 23:59:59') + 1;

// Check the date range.
if ($date_start > $date_end) {
	pines_notice('Invalid date range given.');
	pines_redirect(pines_url('com_hrm', 'employee/timeclock/view'));
	$module->detach();
	return;
}

// Get all the employees
$employee_ids = explode(',', $_REQUEST['employees']);
$module->employees = array();
foreach ($employee_ids as $cur_id) {
	$cur_employee = com_hrm_employee::factory((int) $cur_id);
	if (!isset($cur_employee->guid))
		continue;
	// Don't let the user see other employees without permission.
	if (!gatekeeper('com_hrm/viewclock') && !$_SESSION['user']->is($cur_employee)) {
		pines_notice('You only have the ability to view your own timeclock.');
		continue;
	}
	if ($module->local_timezones) {
		// Calculate times in the employee's timezone.
		$timezone = $cur_employee->get_timezone();
		date_default_timezone_set($timezone);
		$date_start = strtotime($_REQUEST['date_start']);
		$date_end = strtotime($_REQUEST['date_end']);
	}
	$module->employees[] = array(
		'entity' => $cur_employee,
		'timezone' => $timezone,
		'date_start' => $date_start,
		'date_end' => $date_end
	);
}

// Set the timezone back to the default.
if ($module->local_timezones)
	date_default_timezone_set($cur_timezone);

if (!$module->employees) {
	pines_notice('No valid employees were selected.');
	pines_redirect(pines_url('com_hrm', 'employee/timeclock/view'));
	$module->detach();
	return;
}

?>