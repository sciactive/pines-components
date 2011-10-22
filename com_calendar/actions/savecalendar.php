<?php
/**
 * Save all of the events for the company calendar.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('com_calendar/editcalendar') && !gatekeeper('com_calendar/managecalendar'))
	punt_user(null, pines_url('com_calendar', 'editcalendar'));
$edit_others = gatekeeper('com_calendar/managecalendar') ? true : false;

$pines->page->override = true;
header('Content-Type: text/plain');

$errors = false;
if (isset($_REQUEST['events'])) {
	$events = explode(',', $_REQUEST['events']);

	date_default_timezone_set($_SESSION['user']->get_timezone());
	foreach ($events as $cur_event) {
		if (!empty($cur_event)) {
			$event_details = explode('|', $cur_event);
			$event = com_calendar_event::factory((int) $event_details[0]);
			if (!$edit_others && !$event->user->is($_SESSION['user']))
				continue;

			if (isset($event->employee->guid) && !$event->time_off && !isset($event->appointment))
				$event->color = $event->employee->color;

			$event->event_id = $event_details[1];
			$event->start = strtotime($event_details[2]);
			$event->end = strtotime($event_details[3]);
			$event->all_day = ($event_details[4] == 'true') ? true : false;
			if (!isset($event->appointment)) {
				if ($event->all_day) {
					$days = ceil(($event->end - $event->start) / 86400);
					$event->scheduled = isset($event->employee->workday_length) ? $event->employee->workday_length : $pines->config->com_calendar->workday_length;
					$event->scheduled *= 3600 * $days;
				} else {
					$event->scheduled = $event->end - $event->start;
				}
			} else {
				if (isset($event->appointment->guid)) {
					$existing_appt = $pines->entity_manager->get_entity(
						array('class' => com_customer_interaction),
						array('&',
							'data' => array('status', 'open'),
							'ref' => array('customer', $event->appointment->customer),
							'gte' => array('action_date', $event->start),
							'lte' => array('action_date', $event->end)
						),
						array('!&', 'data' => array('guid', $event->appointment->guid))
					);
					if (isset($existing_appt->guid) && $event->appointment->guid != $existing_appt->guid) {
						$errors = $event->appointment->customer->name.' is already scheduled for an appointment during this timeslot.';
						continue;
					}
					$event->appointment->action_date = $event->start;
					$event->appointment->save();
				}
			}
			$event->ac->other = 1;
			$event->save();
		}
	}
}
$pines->page->override_doc($errors);

?>