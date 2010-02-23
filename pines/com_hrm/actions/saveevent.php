<?php
/**
 * Save a new event for the company calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editcalendar') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editcalendar', null, false));

if (isset($_REQUEST['employee'])) {
	if ($_REQUEST['event_date'] != 'Date') {
		$event_month = date('n', strtotime($_REQUEST['event_date']));
		$event_day = date('j', strtotime($_REQUEST['event_date']));
		$event_year = date('Y', strtotime($_REQUEST['event_date']));
		if ($_REQUEST['event_enddate']) {
			$event_endmonth = date('n', strtotime($_REQUEST['event_enddate']));
			$event_endday = date('j', strtotime($_REQUEST['event_enddate']));
			$event_endyear = date('Y', strtotime($_REQUEST['event_enddate']));
		}
	} else {
		$event_month = date('n');
		$event_day = date('j');
		$event_year = date('Y');
	}
	if (isset($_REQUEST['id'])) {
		$event = com_hrm_event::factory((int) $_REQUEST['id']);
		if (is_null($event->guid)) {
			display_error('The calendar was altered while editing the event.');
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
	if ($_REQUEST['event_enddate']) {
		$event->end = mktime($_REQUEST['event_end'],0,0,$event_endmonth,$event_endday,$event_endyear);
	} else {
		$event->end = mktime($_REQUEST['event_end'],0,0,$event_month,$event_day,$event_year);
	}
	$event->all_day = ($_REQUEST['all_day'] == 'allDay') ? true: false;
	if ($pines->config->com_hrm->global_events)
		$event->ac->other = 1;

	if ($event->save()) {
		$action = ( isset($_REQUEST['id']) ) ? 'Saved ' : 'Entered ';
		display_notice($action.'['.$event->title.']');
	} else {
		display_error('Error saving event. Do you have permission?');
	}
}

$pines->com_hrm->show_calendar();
?>