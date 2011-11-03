<?php
/**
 * Edit an employee's timeclock history.
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

if ( !gatekeeper('com_hrm/manageclock') )
	punt_user(null, pines_url('com_hrm', 'employee/timeclock/edit', array('id' => $_REQUEST['id'])));

$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
if (!isset($employee->guid)) {
	pines_error('Requested employee id is not accessible.');
	return;
}

// Calculate times in the employee's timezone.
$cur_timezone = date_default_timezone_get();
date_default_timezone_set($employee->get_timezone());
if (empty($_REQUEST['time_start']))
	$time_start = strtotime('last monday');
else
	$time_start = strtotime($_REQUEST['time_start']);
if (empty($_REQUEST['time_end']))
	$time_end = strtotime('this monday');
else
	$time_end = strtotime($_REQUEST['time_end']);
date_default_timezone_set($cur_timezone);

$employee->timeclock->print_timeclock($time_start, $time_end);

?>