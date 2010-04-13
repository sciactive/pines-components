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
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportattendance') )
	punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'reportattendance'));
if ($_REQUEST['employee'] === '') {
	pines_notice('Please select an employee.');
	unset($_REQUEST['employee']);
}

if ( isset($_REQUEST['start']) ) {
	$employee = empty($_REQUEST['employee']) ? null : com_hrm_employee::factory((int) $_REQUEST['employee']);
	$location = empty($_REQUEST['location']) ? null : group::factory((int) $_REQUEST['location']);
	$start = strtotime($_REQUEST['start']);
	$end = strtotime($_REQUEST['end']);
	$pines->com_reports->report_attendance($start, $end, $location, $employee);
} else {
	// strtotime('next monday', time() - 604800) gets today if it's Monday, or the last Monday.
	$pines->com_reports->report_attendance(strtotime('next monday', time() - 604800), time(), $_SESSION['user']->group);
}
?>