<?php
/**
 * Edit an event in the company schedule.
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

if ( !gatekeeper('com_calendar/editcalendar') && !gatekeeper('com_calendar/managecalendar'))
	punt_user(null, pines_url('com_calendar', 'editevent'));

$event = com_calendar_event::factory((int)$_REQUEST['id']);
if (!isset($event->guid)) {
	$event = com_calendar_event::factory();
	if (!empty($_REQUEST['start'])) {
		$event->start = strtotime(preg_replace('/\(.*/', '', $_REQUEST['start']));
		$event->end = strtotime(preg_replace('/\(.*/', '', $_REQUEST['end']));
		if ($event->start == $event->end)
			$event->all_day = true;
	}
} else {
	if (!gatekeeper('com_calendar/managecalendar') && !$event->employee->is($_SESSION['user'])) {
		pines_error('You cannot only edit your own events.');
		$pines->com_calendar->show_calendar();
		return;
	}
	// Change the timezone to enter the event with the user's timezone.
	date_default_timezone_set($_SESSION['user']->get_timezone());
}

if (isset($event->appointment)) {
	pines_error('You cannot edit appointments.');
	$pines->com_calendar->show_calendar();
	return;
}

if (isset($_REQUEST['location']))
	$location = group::factory((int)$_REQUEST['location']);

$event->print_form($location);

?>