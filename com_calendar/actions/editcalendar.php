<?php
/**
 * Edit calendar.
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

if ( !gatekeeper('com_calendar/viewcalendar') && !gatekeeper('com_calendar/editcalendar') )
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

if (!empty($_REQUEST['view_type'])) {
	$view_type = $_REQUEST['view_type'];
} else {
	$view_type = 'agendaWeek';
}

if (!empty($_REQUEST['start'])) {
	$start = strtotime($_REQUEST['start']);
	$end = strtotime($_REQUEST['end']);
} else {
	$start = strtotime('next monday', time() - 604800);
	$end = strtotime('next monday');
}

$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid))
	$location = null;

$employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
if (!isset($employee->guid))
	$employee = null;

$descendents = ($_REQUEST['descendents'] == 'true');
$filter = !empty($_REQUEST['filter']) ? $_REQUEST['filter'] : 'all';

$pines->com_calendar->show_calendar($view_type, $start, $end, $location, $employee, $descendents, $filter);

?>