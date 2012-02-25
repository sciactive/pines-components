<?php
/**
 * List employee info for a time and attendance report.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/attendance') )
	punt_user(null, pines_url('com_reports', 'attendance/hoursclocked', $_GET));

if (!empty($_REQUEST['start_date'])) {
	$start_date = $_REQUEST['start_date'];
	if (strpos($start_date, '-') === false)
		$start_date = format_date($start_date, 'date_sort');
	$start_date = strtotime($start_date.' 00:00:00');
} else {
	$start_date = strtotime('-1 week');
}
if (!empty($_REQUEST['end_date'])) {
	$end_date = $_REQUEST['end_date'];
	if (strpos($end_date, '-') === false)
		$end_date = format_date($end_date, 'date_sort');
	$end_date = strtotime($end_date.' 23:59:59') + 1;
} else {
	$end_date = strtotime('now');
}
if ($_REQUEST['all_time'] == 'true') {
	$start_date = null;
	$end_date = null;
}

$employee = empty($_REQUEST['employee']) ? null : com_hrm_employee::factory((int) $_REQUEST['employee']);
$location = empty($_REQUEST['location']) ? null : group::factory((int) $_REQUEST['location']);
$descendants = ($_REQUEST['descendants'] == 'true');

$pines->com_reports->hours_clocked($start_date, $end_date, $location, $employee, $descendants);

?>