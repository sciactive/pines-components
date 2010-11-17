<?php
/**
 * Save a new event for the company calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editcalendar') )
	punt_user(null, pines_url('com_hrm', 'editcalendar'));

if (isset($_REQUEST['employee'])) {
	if (isset($_REQUEST['start'])) {
		$start_time = strtotime($_REQUEST['start']);
		$end_time = strtotime($_REQUEST['end']);
		$event_month = date('n', $start_time);
		$event_day = date('j', $start_time);
		$event_year = date('Y', $start_time);
		$event_endmonth = date('n', $end_time);
		$event_endday = date('j', $end_time);
		$event_endyear = date('Y', $end_time);
	} else {
		// Default to the current date.
		$event_month = date('n');
		$event_day = date('j');
		$event_year = date('Y');
		$event_endmonth = date('n');
		$event_endday = date('j');
		$event_endyear = date('Y');
	}
	if (isset($_REQUEST['id'])) {
		$event = com_hrm_event::factory((int) $_REQUEST['id']);
		if (!isset($event->guid)) {
			pines_error('The calendar was altered while editing the event.');
			$pines->com_hrm->show_calendar();
			return;
		}
		if ($event->time_off)
			return;
	} else {
		$event = com_hrm_event::factory();
	}

	$event->employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
	$location = $event->employee->group;
	$event->title = $event->employee->name;
	$event->color = $event->employee->color;

	if (!isset($event->employee->guid)) {
		unset($event->employee);
		$location = group::factory((int) $_REQUEST['location']);
		if (!isset($location->guid)) {
			pines_error('The specified location for this event does not exist.');
			$pines->com_hrm->show_calendar();
			return;
		}
		$event_details = explode(':', $_REQUEST['employee']);
		$event->title = $event->district = ($event_details[0] == 'District') ? $location->name : $event_details[0];
		$event->color = $event_details[1];
	}

	if ($_REQUEST['event_label'] != 'Label') {
		$event->label = $_REQUEST['event_label'];
		$event->title = $event->label .' - '. $event->title;
	}
	$event->private = ($_REQUEST['private_event'] == 'true');
	$event->all_day = ($_REQUEST['all_day'] == 'true');
	$start_hour = ($_REQUEST['time_start_ampm'] == 'am') ? $_REQUEST['time_start_hour'] : $_REQUEST['time_start_hour'] + 12;
	$end_hour = ($_REQUEST['time_end_ampm'] == 'am') ? $_REQUEST['time_end_hour'] : $_REQUEST['time_end_hour'] + 12;
	// Change the timezone to enter the event with the user's timezone.
	date_default_timezone_set($_SESSION['user']->get_timezone());
	$event->start = mktime($event->all_day ? 0 : $start_hour,$_REQUEST['time_start_minute'],0,$event_month,$event_day,$event_year);
	$event->end = mktime($event->all_day ? 23 : $end_hour,$event->all_day ? 59 : $_REQUEST['time_end_minute'],$event->all_day ? 59 : 0,$event_month,$event_day,$event_year);
	// If the start and end dates are the same, push the end date ahead one day.
	if ($event->start >= $event->end)
		$event->end = strtotime('+1 day', $event->start);

	if ($event->all_day) {
		$days = ceil(($event->end - $event->start) / 86400);
		$event->scheduled = isset($event->employee->workday_length) ? $event->employee->workday_length : $pines->config->com_hrm->workday_length;
		$event->scheduled *= 3600 * $days;
	} else {
		$event->scheduled = $event->end - $event->start;
	}

	$event->ac->other = 1;

	if ($event->save()) {
		$event->group = $location;
		$event->save();
		$action = ( isset($_REQUEST['id']) ) ? 'Saved ' : 'Entered ';
		pines_notice($action.'['.$event->title.']');
	} else {
		pines_error('Error saving event. Do you have permission?');
	}
	// Go back to the employee's calendar.
	$employee = ($_REQUEST['employee_view'] == 'true') ? $event->employee : null;
} else {
	$employee = null;
}
redirect(pines_url('com_hrm', 'editcalendar', array('location' => $location->guid, 'employee' => $employee->guid)));
?>