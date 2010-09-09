<?php
/**
 * Edit calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/viewcalendar') && !gatekeeper('com_hrm/editcalendar') )
	punt_user(null, pines_url('com_hrm', 'editcalendar'));

$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid))
	$location = null;

$employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
if (!isset($employee->guid))
	$employee = null;

$pines->com_hrm->show_calendar((int) $_REQUEST['id'], $location, $employee, (int) $_REQUEST['rto_id']);

?>