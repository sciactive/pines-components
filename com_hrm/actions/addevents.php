<?php
/**
 * Add duplicate events to the company calendar.
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

if (isset($_REQUEST['events'])) {
	$count = count((array) $_REQUEST['events']);
	if ($count > 1)
		$dupe_id = mt_rand();
	foreach ((array) $_REQUEST['events'] as $cur_event) {
		// The event that will be duplicated.
		$event = com_hrm_event::factory((int) $cur_event);
		if (!isset($event->guid) || $event->time_off)
			continue;
		// Create a new event to be our duplicate.
		$dupe_event = com_hrm_event::factory();
		// Duplicate the data for the new event.
		if ($count > 1)
			$dupe_event->id = $dupe_id;
		$dupe_event->title = $event->title;
		$dupe_event->label = $event->label;
		$dupe_event->employee = $event->employee;
		$dupe_event->start = $event->start;
		$dupe_event->end = $event->end;
		$dupe_event->color = $event->color;
		$dupe_event->all_day = $event->all_day;
		if ($pines->config->com_hrm->global_events)
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