<?php
/**
 * Get the requested calendar events, returning JSON.
 *
 * @package Components\calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_calendar/viewcalendar') && !gatekeeper('com_calendar/editcalendar') )
	punt_user(null, pines_url('com_calendar', 'events_json', $_GET));

$pines->page->override = true;
header('Content-Type: application/json');

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
	$start = (int) $_REQUEST['start'];
	$end = (int) $_REQUEST['end'];
} else {
	$start = strtotime('-1 week', strtotime('next monday'));
	$end = strtotime('next monday');
}
date_default_timezone_set($cur_timezone);

$descendants = ($_REQUEST['descendants'] == 'true');
$filter = !empty($_REQUEST['filter']) ? $_REQUEST['filter'] : 'all';

// Get the events.
$events = $pines->com_calendar->get_events($start, $end, $timezone, $location, $employee, $descendants, $filter);

// Build a JSON structure to return.
$struct = array();
foreach ($events as $cur_event) {
	if (!gatekeeper('com_calendar/managecalendar') && $cur_event->private) {
		if (!isset($cur_event->employee->guid) && !$cur_event->group->is($location))
			continue;
		if (isset($cur_event->employee->guid) && !$cur_event->employee->is($_SESSION['user']))
			continue;
	}
	if (!isset($cur_event->user->guid))
		continue;
	// Get the start hour of the first event.
	$cur_start = format_date($cur_event->start, 'custom', 'G', $timezone);
	if (!$cur_event->all_day && ($cur_start < $min_start || !isset($min_start)))
		$min_start = $cur_start;

	// Build a JSON structure.
	$cur_struct = array();
	if ($cur_event->event_id != 0) {
		$cur_struct['group'] = true;
		$cur_struct['id'] = $cur_event->event_id;
		$cur_struct['_id'] = $cur_event->event_id;
		$cur_struct['guid'] = $cur_event->guid;
	} else {
		$cur_struct['group'] = false;
		$cur_struct['id'] = $cur_event->guid;
		$cur_struct['_id'] = $cur_event->guid;
	}
	$cur_struct['title'] = $cur_event->title;
	$cur_struct['start'] = format_date($cur_event->start, 'custom', 'Y-m-d H:i', $timezone);
	$cur_struct['end'] = format_date($cur_event->end, 'custom', 'Y-m-d H:i', $timezone);
	$cur_struct['editable'] = !((!gatekeeper('com_calendar/managecalendar') && (!$cur_event->user->is($_SESSION['user']) || $cur_event->appointment)) || $cur_event->time_off);
	if (isset($cur_event->appointment->guid)) {
		$cur_struct['appointment'] = $cur_event->appointment->guid;
		if ($cur_event->appointment->status == 'open') {
			if ($cur_event->appointment->action_date < strtotime('-3 days'))
				$cur_struct['className'] = 'red';
			elseif ($cur_event->appointment->action_date < strtotime('-1 hour'))
				$cur_struct['className'] = 'yellow';
			else
				$cur_struct['className'] = 'greenyellow';
		} else {
			$cur_struct['className'] = (string) $cur_event->color;
		}
	} else {
		$cur_struct['appointment'] = '';
		$cur_struct['className'] = (string) $cur_event->color;
	}
	$cur_struct['allDay'] = (bool) $cur_event->all_day;
	$cur_struct['info'] = (!empty($cur_event->information)) ? $cur_event->information : '';

	// Append to the JSON array.
	$struct[] = $cur_struct;
}

// Return the JSON structure.
$pines->page->override_doc(json_encode($struct));

?>