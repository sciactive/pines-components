<?php
/**
 * Save a work lineup for a company location.
 *
 * @package Components
 * @subpackage calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_calendar/managecalendar') )
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid)) {
	pines_error('The specified location does not exist.');
	pines_redirect(pines_url('com_calendar', 'editcalendar'));
	return;
}
$shifts = explode(',', $_REQUEST['shifts']);
$cur_timezone = date_default_timezone_get();
foreach ($shifts as $cur_shift) {
	$shift_info = explode('|', $cur_shift);
	$shift_date = $shift_info[0];
	$shift_time = explode('-', $shift_info[1]);
	$shift_start = explode(':', $shift_time[0]);
	$shift_end = explode(':', $shift_time[1]);
	$employee = com_hrm_employee::factory((int) $shift_info[2]);

	if (!isset($employee->guid)) {
		pines_error('The specified employee ['.$shift_info[2].'] does not exist.');
		continue;
	}

	// Enter schedule in employee's timezone.
	date_default_timezone_set($employee->get_timezone());
	$event = com_calendar_event::factory();
	$event->all_day = false;
	$event->employee = $employee;
	$event->title = $employee->name;
	$event->color = $employee->color;
	$event->information = 'Scheduled shift';
	$shift_month = date('n', strtotime($shift_date));
	$shift_day = date('j', strtotime($shift_date));
	$shift_year = date('Y', strtotime($shift_date));
	$event->start = mktime((int) $shift_start[0], (int) $shift_start[1], 0, $shift_month, $shift_day, $shift_year);
	$event->end = mktime((int) $shift_end[0], (int) $shift_end[1], 0, $shift_month, $shift_day, $shift_year);
	$event->scheduled = $event->end - $event->start;
	$event->ac->other = 1;

	if (!$event->save()) {
		$failed_entries .= (empty($failed_entries) ? '' : ', ').$cur_shift;
	} else {
		if (!isset($first_date) || $event->start < $first_date)
			$first_date = $event->start;
		if (!isset($last_date) || $event->end > $last_date)
			$last_date = $event->end;
		$event->group = $employee->group;
		$event->save();
	}
}

if (empty($failed_entries)) {
	pines_notice('Work lineup entered for '.$location->name);
} else {
	pines_error('Could not schedule work for the following shifts: '.$failed_entries);
}

// Find time range in the location timezone.
$parent = $location;
do {
	$timezone = $parent->timezone;
	$parent = $parent->parent;
} while(empty($timezone) && isset($parent->guid));
if (empty($timezone))
	$timezone = $pines->config->timezone;
date_default_timezone_set($timezone);

$total_time = $last_date - $first_date;
if ($total_time <= 86400) {
	$view_type = 'agendaDay';
	// Make query of entities include the whole day.
	$last_date = strtotime('23:59:59', $last_date) + 1;
} elseif ($total_time <= 604800) {
	$view_type = 'agendaWeek';

	// This changes the end date of the entities query to be the beginning of the week.
	if ((int) date('N', $last_date) < 7)
		$last_date = strtotime('next sunday', $last_date);
	$last_date = strtotime('23:59:59', $last_date) + 1;

	// This changes the beginning date to be the first of the week.
	if ((int) date('N', $first_date) > 1)
		$first_date = strtotime('last monday', $first_date);
	$first_date = strtotime('00:00:00', $first_date);
} else {
	$view_type = 'month';

	// This includes the query of entities to the beginning of the month.
	$first_date = strtotime('1st', $first_date);

	// This makes sure to include to the end of the month.
	$last_date = strtotime('+1 month', strtotime('1st', $last_date));
}
date_default_timezone_set($cur_timezone);

pines_redirect(pines_url('com_calendar', 'editcalendar', array('view_type' => $view_type, 'start' => format_date($first_date, 'date_sort', '', $timezone), 'end' => format_date($last_date, 'date_sort', '', $timezone), 'location' => $location->guid)));

?>