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
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_calendar/viewcalendar') && !gatekeeper('com_calendar/editcalendar') )
	punt_user(null, pines_url('com_calendar', 'editcalendar'));

$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid))
	$location = null;

$employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
if (!isset($employee->guid))
	$employee = null;

$descendents = ($_REQUEST['descendents'] == 'true');
$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : 'all';

$pines->com_calendar->show_calendar($location, $employee, $descendents, $filter);

?>