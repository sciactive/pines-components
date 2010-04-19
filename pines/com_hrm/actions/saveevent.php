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

if ( !gatekeeper('com_sales/editcalendar') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editcalendar'));

if (isset($_REQUEST['employee'])) {
	if ($_REQUEST['event_date'] != 'Date') {
		$event_month = date('n', strtotime($_REQUEST['event_date']));
		$event_day = date('j', strtotime($_REQUEST['event_date']));
		$event_year = date('Y', strtotime($_REQUEST['event_date']));
		$event_endmonth = date('n', strtotime($_REQUEST['event_enddate']));
		$event_endday = date('j', strtotime($_REQUEST['event_enddate']));
		$event_endyear = date('Y', strtotime($_REQUEST['event_enddate']));
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
	} else {
		$event = com_hrm_event::factory();
	}
	$event_details = explode(':', $_REQUEST['employee']);
	$event->id = 0;
	$event->employee = $event_details[0];
	if ($_REQUEST['event_label'] != 'Label') {
		$event->label = $_REQUEST['event_label'];
		$event->title = $event->label .' - '. $event->employee;
	} else {
		$event->title = $event->employee;
	}
	$event->color = $event_details[1];
	$event->start = mktime($_REQUEST['event_start'],0,0,$event_month,$event_day,$event_year);
	$event->end = mktime($_REQUEST['event_end'],0,0,$event_endmonth,$event_endday,$event_endyear);
	$event->all_day = ($_REQUEST['all_day'] == 'allDay') ? true: false;
	if ($pines->config->com_hrm->global_events)
		$event->ac->other = 1;

	if ($event->save()) {
		$action = ( isset($_REQUEST['id']) ) ? 'Saved ' : 'Entered ';
		pines_notice($action.'['.$event->title.']');
	} else {
		pines_error('Error saving event. Do you have permission?');
	}
}

$pines->com_hrm->show_calendar();
?>