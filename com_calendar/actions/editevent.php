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
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user(null, pines_url('com_calendar', 'editevent'));

$event = com_calendar_event::factory((int)$_REQUEST['id']);
if (!isset($event->guid))
	$event = com_calendar_event::factory();

if ($event->appointment && !gatekeeper('com_calendar/editappointments')) {
	pines_error('You cannot edit appointments.');
	$pines->com_calendar->show_calendar();
	return;
}

if (isset($_REQUEST['location']))
	$location = group::factory((int)$_REQUEST['location']);

$event->print_form($location);

?>