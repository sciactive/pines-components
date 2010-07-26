<?php
/**
 * Save a work schedule for an employee.
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
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editcalendar'));

if (isset($_REQUEST['employee'])) {	
	$employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
	if (!isset($employee->guid)) {
		pines_error('The specified employee for this schedule does not exist.');
		$pines->com_hrm->show_calendar();
		return;
	}
	
	if (empty($_REQUEST['dates'])) {
		pines_notice('No dates were specified to schedule work for.');
		$pines->com_hrm->show_calendar(null, null, $employee);
		return;
	}
	$dates = explode(',', $_REQUEST['dates']);
	$all_day = ($_REQUEST['all_day'] == 'true');
	$location = $employee->group;
	foreach ($dates as $cur_date) {
		$event = com_hrm_event::factory();
		$event->all_day = $all_day;
		$event->employee = $employee;
		$event->title = $employee->name;
		$event->color = $employee->color;
		$event_month = date('n', strtotime($cur_date));
		$event_day = date('j', strtotime($cur_date));
		$event_year = date('Y', strtotime($cur_date));
		$event->start = mktime($event->all_day ? 0 : $_REQUEST['time_start'],0,0,$event_month,$event_day,$event_year);
		$event->end = mktime($event->all_day ? 23 : $_REQUEST['time_end'],$event->all_day ? 59 : 0,$event->all_day ? 59 : 0,$event_month,$event_day,$event_year);

		if ($event->all_day) {
			$days = ceil(($event->end - $event->start) / 86400);
			$event->scheduled = isset($event->employee->workday_length) ? $event->employee->workday_length : $pines->config->com_hrm->workday_length;
			$event->scheduled *= 3600 * $days;
		} else {
			$event->scheduled = $event->end - $event->start;
		}

		if ($pines->config->com_hrm->global_events)
			$event->ac->other = 1;

		if (!$event->save()) {
			$failed_saves .= (empty($failed_saves) ? '' : ', ').$cur_date;
		} else {
			$event->group = $location;
			$event->save();
		}
	}

	if (empty($failed_removes)) {
		pines_notice('Work schedule entered for '.$employee->name);
	} else {
		pines_error('Could not schedule work for the following dates: '.$failed_saves);
	}
}

redirect(pines_url('com_hrm', 'editcalendar', array('employee' => $employee->guid)));

?>