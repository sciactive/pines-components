<?php
/**
 * Save a new event for the company calendar.
 *
 * @package Components\calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_calendar/editcalendar'))
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

$pines->page->override = true;
header('Content-Type: application/json');

if (!isset($_REQUEST['employee'])) {
	$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'Please choose an employee for this event.')));
	return;
}
if (!empty($_REQUEST['id'])) {
	$event = com_calendar_event::factory((int) $_REQUEST['id']);
	if (!isset($event->guid)) {
		$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'The calendar was altered while editing the event.')));
		return;
	}
	if (isset($event->appointment)) {
		$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'You cannot edit appointments.')));
		return;
	}
	if ($event->time_off) {
		$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'You cannot edit time off.')));
		return;
	}
	if (!gatekeeper('com_calendar/managecalendar') && !($event->user->guid && $_SESSION['user']->is($event->user))) {
		$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'You can only edit your own events.')));
		return;
	}
} else {
	$event = com_calendar_event::factory();
}

// Use requested timezone.
$cur_timezone = date_default_timezone_get();
date_default_timezone_set($_REQUEST['timezone']);

$event->employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
if (!gatekeeper('com_calendar/managecalendar'))
	$event->employee = com_hrm_employee::factory($_SESSION['user']->guid);

$location = $event->employee->group;
$event->title = $event->employee->name;
$event->color = $event->employee->color;

if (!isset($event->employee->guid)) {
	unset($event->employee);
	$location = group::factory((int) $_REQUEST['location']);
	if (!isset($location->guid)) {
		$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'The specified location for this event does not exist.')));
		date_default_timezone_set($cur_timezone);
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
	$event->end = strtotime(format_date($event->start, 'date_sort').' 11:59 PM');

$event->ac->other = 1;

if ($event->save()) {
	$event->group = $location;
	$event->save();
	$action = ( isset($_REQUEST['id']) ) ? 'Saved' : 'Entered';
	$pines->page->override_doc(json_encode(array('result' => true, 'message' => $action.' ['.$event->title.']')));
} else {
	$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'Error saving event. Do you have permission?')));
}

date_default_timezone_set($cur_timezone);

?>