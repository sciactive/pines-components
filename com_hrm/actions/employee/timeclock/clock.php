<?php
/**
 * Clock an employee in or out, returning their status in JSON.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/clock') && !gatekeeper('com_hrm/manageclock') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'employee/timeclock/clock', $_REQUEST));

$pines->page->override = true;

if ($_REQUEST['id'] == 'self') {
	$employee = com_hrm_employee::factory($_SESSION['user']->guid);
} else {
	if ( !gatekeeper('com_hrm/manageclock') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'employee/timeclock/clock', $_REQUEST));
	$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
}

if (!isset($employee->guid)) {
	$pines->page->override_doc('false');
	return;
}

if (!empty($employee->timeclock) && $employee->timeclock[count($employee->timeclock) - 1]['status'] == 'in') {
	$employee->timeclock[] = array('status' => 'out', 'time' => time());
} else {
	$employee->timeclock[] = array('status' => 'in', 'time' => time());
}

$entry = $employee->timeclock[count($employee->timeclock) - 1];
$entry['time'] = format_date($entry['time']);
$pines->page->override_doc(json_encode(array($employee->save(), $entry)));

?>