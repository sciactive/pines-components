<?php
/**
 * Save a new event for the company calendar.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_calendar/editcalendar'))
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

if (isset($_REQUEST['employee'])) {
	if (!empty($_REQUEST['id'])) {
		$event = com_calendar_event::factory((int) $_REQUEST['id']);
		if (!isset($event->guid)) {
			pines_error('The calendar was altered while editing the event.');
			redirect(pines_url('com_calendar', 'editcalendar', array('location' => $location->guid, 'employee' => $employee->guid)));
			return;
		}
		if (isset($event->appointment)) {
			pines_error('You cannot edit appointments.');
			redirect(pines_url('com_calendar', 'editcalendar', array('location' => $location->guid, 'employee' => $employee->guid)));
			return;
		}
		if ($event->time_off)
			return;
		if (!gatekeeper('com_calendar/managecalendar') && !$event->user->is($_SESSION['user']))
			punt_user(null, pines_url('com_calendar', 'editcalendar'));
	} else {
		$event = com_calendar_event::factory();
	}

	$event->employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
	if (!gatekeeper('com_calendar/managecalendar'))
		$event->employee = com_hrm_employee::factory((int) $_SESSION['user']->guid);

	$location = $event->employee->group;
	$event->title = $event->employee->name;
	$event->color = $event->employee->color;

	if (!isset($event->employee->guid)) {
		unset($event->employee);
		$location = group::factory((int) $_REQUEST['location']);
		if (!isset($location->guid)) {
			pines_error('The specified location for this event does not exist.');
			$pines->com_calendar->show_calendar();
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
	$event->information = $_REQUEST['information'];
	$event->private = ($_REQUEST['private_event'] == 'true');
	$event->all_day = ($_REQUEST['all_day'] == 'true');

	// Change the timezone to enter the event with the user's timezone.
	date_default_timezone_set($_SESSION['user']->get_timezone());
	if (isset($_REQUEST['start'])) {
		$event->start = strtotime($_REQUEST['start'].$_REQUEST['time_start']);
		$event->end = strtotime($_REQUEST['end'].$_REQUEST['time_end']);
	} else {
		// Default to the current date.
		$event->start = time();
		$event->end = time();
	}
	// If the start and end dates are the same, push the end date ahead one day.
	if ($event->start >= $event->end)
		$event->end = strtotime(format_date($event->start, 'date_short').' 11:59 PM');

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
redirect(pines_url('com_calendar', 'editcalendar', array('location' => $location->guid, 'employee' => $employee->guid, 'descendents' => $_REQUEST['descendents'])));
?>