<?php
/**
 * Add duplicate events to the company calendar.
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

if ( !gatekeeper('com_calendar/editcalendar') )
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

if (isset($_REQUEST['events'])) {
	$count = count((array) $_REQUEST['events']);
	if ($count > 1)
		$dupe_id = mt_rand();
	foreach ((array) $_REQUEST['events'] as $cur_event) {
		// The event that will be duplicated.
		$event = com_calendar_event::factory((int) $cur_event);
		if (!isset($event->guid) || $event->time_off || isset($event->appointment))
			continue;
		// Create a new event to be our duplicate.
		$dupe_event = com_calendar_event::factory();
		// Duplicate the data for the new event.
		if ($count > 1)
			$dupe_event->event_id = $dupe_id;
		$dupe_event->title = $event->title;
		$dupe_event->label = $event->label;
		$dupe_event->employee = $event->employee;
		$dupe_event->start = $event->start;
		$dupe_event->end = $event->end;
		$dupe_event->color = $event->color;
		$dupe_event->private = $event->private;
		$dupe_event->all_day = $event->all_day;
		$dupe_event->ac->other = 1;
		// Save our new duplicate event.
		$dupe_event->save();
		if (!$dupe_event->group->is($event->group)) {
			$dupe_event->group = $event->group;
			$dupe_event->save();
		}
	}
}

?>