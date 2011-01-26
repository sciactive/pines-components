<?php
/**
 * Save a work lineup for a company location.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_calendar/managecalendar') )
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid)) {
	pines_error('The specified location does not exist.');
	$pines->com_calendar->show_calendar();
	return;
}
$shifts = explode(',', $_REQUEST['shifts']);
foreach ($shifts as $cur_shift) {
	$shift_info = explode('|', $cur_shift);
	$shift_date = $shift_info[0];
	$shift_time = explode('-', $shift_info[1]);
	$shift_start = explode(':', $shift_time[0]);
	$shift_end = explode(':', $shift_time[1]);
	$employee = com_hrm_employee::factory((int) $shift_info[2]);

	if (!isset($employee->guid)) {
		pines_error('The specified employee ['.$shift_info[0].'] does not exist.');
		continue;
	}

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
	$event->start = mktime((int)$shift_start[0],(int)$shift_start[1],0,$shift_month,$shift_day,$shift_year);
	$event->end = mktime((int)$shift_end[0],(int)$shift_end[1],0,$shift_month,$shift_day,$shift_year);
	$event->scheduled = $event->end - $event->start;
	$event->ac->other = 1;

	if (!$event->save()) {
		$failed_entries .= (empty($failed_entries) ? '' : ', ').$cur_date;
	} else {
		$event->group = $employee->group;
		$event->save();
	}

}
if (empty($failed_entries)) {
	pines_notice('Work lineup entered for '.$location->name);
} else {
	pines_error('Could not schedule work for the following dates: '.$failed_entries);
}

redirect(pines_url('com_calendar', 'editcalendar', array('location' => $location->guid)));

?>