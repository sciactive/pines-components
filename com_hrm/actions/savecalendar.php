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

if (isset($_REQUEST['events'])) {
	$events = explode(',', $_REQUEST['events']);
	$event_list = array();
	$event_count = 0;

	/* Array attempt.
	foreach ($_REQUEST['events'] as $c_event) {
		$event = com_hrm_event::factory((int) $c_event[0]);
		$event_list[$event_count] = (int) $c_event[0];
		$event_count++;

		$event->id = $c_event[1];
		$event->title = $c_event[2];
		$title_str = explode(' - ', $event->title);
		if (count($title_str) > 1) {
			$event->label = $title_str[0];
			$event->employee = $title_str[1];
		} else {
			$event->employee = $event->title;
		}
		$event->start = strtotime($c_event[3]);
		$event->end = strtotime($c_event[4]);
		$event->color = $c_event[5];
		$event->all_day = ($c_event[6] == 'true') ? true : false;
		if ($pines->config->com_hrm->global_events)
			$event->ac->other = 1;
		$event->save();
	}
	*/
	
	foreach ($events as $cur_event) {
		if (!empty($cur_event)) {
			$event_details = explode('|', $cur_event);
			$event = com_hrm_event::factory((int) $event_details[0]);
			$event_list[$event_count] = (int) $event_details[0];
			$event_count++;
			
			$event->id = $event_details[1];
			$event->title = $event_details[2];
			$title_str = explode(' - ', $event->title);
			if (count($title_str) > 1) {
				$event->label = $title_str[0];
				$event->employee = $title_str[1];
			} else {
				$event->employee = $event->title;
			}
			$event->start = strtotime($event_details[3]);
			$event->end = strtotime($event_details[4]);
			$event->color = $event_details[5];
			$event->all_day = ($event_details[6] == 'true') ? true : false;
			if ($pines->config->com_hrm->global_events)
				$event->ac->other = 1;
			$event->save();
		}
	}
	$existing_events = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'event'), 'class' => com_hrm_event));
	foreach ($existing_events as $cur_event) {
		if (!in_array($cur_event->guid, $event_list))
			$cur_event->delete();
	}
}

?>