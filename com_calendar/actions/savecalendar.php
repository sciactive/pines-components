<?php
/**
 * Save all of the events for the company calendar.
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

if (!gatekeeper('com_calendar/editcalendar') && !gatekeeper('com_calendar/managecalendar'))
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

$pines->page->override = true;
header('Content-Type: text/plain');

// Run the action in the given timezone.
$cur_timezone = date_default_timezone_get();
date_default_timezone_set($_REQUEST['timezone']);

$errors = array();
$events = json_decode($_REQUEST['events'], true);
$edit_others = gatekeeper('com_calendar/managecalendar');

foreach ($events as $cur_event) {
	if (empty($cur_event))
		continue;
	$event = com_calendar_event::factory((int) $cur_event['id']);
	if (!$edit_others && !$_SESSION['user']->is($event->user))
		continue;

	if (isset($event->employee->guid) && !$event->time_off && !isset($event->appointment))
		$event->color = $event->employee->color;

	$event->event_id = $cur_event['_id'];
	$event->start = strtotime($cur_event['start']);
	$event->end = strtotime($cur_event['end']);
	$event->all_day = (bool) $cur_event['all_day'];
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
						'tag' => array('com_customer', 'interaction'),
						'strict' => array('status', 'open'),
						'gte' => array('action_date', $event->start),
						'lte' => array('action_date', $event->end),
						'ref' => array('customer', $event->appointment->customer)
					),
					array('!&',
						'guid' => $event->appointment->guid
					)
				);
			if (isset($existing_appt->guid) && $event->appointment->guid != $existing_appt->guid) {
				$errors[] = "{$event->appointment->customer->name} is already scheduled for an appointment during this timeslot.";
				continue;
			}
			$event->appointment->action_date = $event->start;
			$event->appointment->save();
		}
	}
	$event->ac->other = 1;
	if (!$event->save())
		$errors[] = 'Event starting at '.format_date($event->start, 'full_short').' and ending at '.format_date($event->end, 'full_short').' couldn\'t be saved. Do you have permission?';
}
$pines->page->override_doc(implode("\n", $errors));

date_default_timezone_set($cur_timezone);

?>