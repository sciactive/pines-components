<?php
/**
 * Save all of the events for the company calendar.
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

$pines->page->override = true;

if (isset($_REQUEST['events'])) {
	$events = explode(',', $_REQUEST['events']);
	$event_list = array();
	$event_count = 0;
	$location = group::factory((int) $_REQUEST['location']);
	if (!isset($location->guid)) {
		pines_error('The specified location of this schedule does not exist.');
		$pines->com_hrm->show_calendar();
		return;
	}
	/* Array attempt.
	foreach ($_REQUEST['events'] as $c_event) {
		$event = com_hrm_event::factory((int) $c_event[0]);
		$event_list[$event_count] = (int) $c_event[0];
		$event_count++;

		$event->id = $c_event[1];
		$event->start = strtotime($c_event[2]);
		$event->end = strtotime($c_event[3]);
		$event->all_day = ($c_event[4] == 'true') ? true : false;
		if ($pines->config->com_hrm->global_events)
			$event->ac->other = 1;
		$event->save();
	}
	*/

	foreach ($events as $cur_event) {
		if (!empty($cur_event)) {
			$event_details = explode('|', $cur_event);
			$event = com_hrm_event::factory((int) $event_details[0]);
			$event_list[] = (int) $event_details[0];
			
			$event->id = $event_details[1];
			$event->start = strtotime($event_details[2]);
			$event->end = strtotime($event_details[3]);
			$event->all_day = ($event_details[4] == 'true') ? true : false;
			if ($event->all_day) {
				$days = ceil(($event->end - $event->start) / 86400);
				$event->scheduled = isset($event->employee->workday_length) ? $event->employee->workday_length : $pines->config->com_hrm->workday_length;
				$event->scheduled *= 3600 * $days;
			} else {
				$event->scheduled = $event->end - $event->start;
			}
			if ($pines->config->com_hrm->global_events)
				$event->ac->other = 1;
			$event->save();
		}
	}
	$deleted_events = $pines->entity_manager->get_entities(
			array('class' => com_hrm_event),
			array('!&',
				'guid' => $event_list,
				'data' => array('time_off', true)
			),
			array('&',
				'ref' => array('group', $location),
				'tag' => array('com_hrm', 'event')
			)
		);
	foreach ($deleted_events as $cur_event) {
		if (!in_array($cur_event->guid, $event_list))
			$cur_event->delete();
	}
}

?>