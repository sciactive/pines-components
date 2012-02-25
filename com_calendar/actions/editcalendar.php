<?php
/**
 * Edit calendar.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_calendar/viewcalendar') && !gatekeeper('com_calendar/editcalendar') )
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

if (!empty($_REQUEST['view_type'])) {
	$view_type = $_REQUEST['view_type'];
} else {
	$view_type = 'agendaWeek';
}

$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid))
	$location = null;

$employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
if (!isset($employee->guid))
	$employee = null;
if (!isset($employee) && !isset($location))
	$employee = $_SESSION['user'];

// Get the timezone.
if (isset($employee->guid))
	$timezone = $employee->get_timezone();
else {
	$parent = $location;
	do {
		$timezone = $parent->timezone;
		$parent = $parent->parent;
	} while(empty($timezone) && isset($parent->guid));
}
if (empty($timezone))
	$timezone = $pines->config->timezone;

// Calculate using correct timezone.
$cur_timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
if (!empty($_REQUEST['start'])) {
	$start = strtotime($_REQUEST['start']);
	$end = strtotime($_REQUEST['end']);
} else {
	$start = strtotime('-1 week', strtotime('next monday'));
	$end = strtotime('next monday');
}
date_default_timezone_set($cur_timezone);

$descendants = ($_REQUEST['descendants'] == 'true');
$filter = !empty($_REQUEST['filter']) ? $_REQUEST['filter'] : 'all';

$pines->com_calendar->show_calendar($view_type, $start, $end, $timezone, $location, $employee, $descendants, $filter);

?>