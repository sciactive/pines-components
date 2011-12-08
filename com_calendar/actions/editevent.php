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

// Use the correct timezone.
if (!empty($_REQUEST['timezone'])) {
	$timezone = $_REQUEST['timezone'];
} elseif (isset($event->user->guid)) {
	$timezone = $event->user->get_timezone;
} else {
	$timezone = $_SESSION['user']->get_timezone();
}
$cur_timezone = date_default_timezone_get();
date_default_timezone_set($timezone);

if (!isset($event->guid)) {
	$event = com_calendar_event::factory();
	if (!empty($_REQUEST['start'])) {
		// Fix the stupid time string that has the wrong timezone on it. I hate JavaScript's Date object.
		$event->start = strtotime(preg_replace('/ ?(\w{3,4}-\d{4})? ?(\(\w+\))?$/', '', $_REQUEST['start']));
		$event->end = strtotime(preg_replace('/ ?(\w{3,4}-\d{4})? ?(\(\w+\))?$/', '', $_REQUEST['end']));
		if ($event->start == $event->end)
			$event->all_day = true;
	}
} else {
	if (!gatekeeper('com_calendar/managecalendar') && !$event->employee->is($_SESSION['user'])) {
		pines_error('You cannot only edit your own events.');
		pines_redirect(pines_url('com_calendar', 'editcalendar'));
		date_default_timezone_set($cur_timezone);
		return;
	}
}

if (isset($event->appointment)) {
	pines_error('You cannot edit appointments.');
	pines_redirect(pines_url('com_calendar', 'editcalendar'));
	return;
}

if (isset($_REQUEST['location']))
	$location = group::factory((int)$_REQUEST['location']);

date_default_timezone_set($cur_timezone);

$event->print_form($location, $timezone);

?>