<?php
/**
 * List all employee conduct issues.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportissues') )
	punt_user(null, pines_url('com_reports', 'reportissues'));

if ($_REQUEST['employee'] === '') {
	pines_notice('Please select an employee.');
	unset($_REQUEST['employee']);
}

if ( isset($_REQUEST['start']) ) {
	$employee = empty($_REQUEST['employee']) ? null : com_hrm_employee::factory((int) $_REQUEST['employee']);
	$location = empty($_REQUEST['location']) ? null : group::factory((int) $_REQUEST['location']);
	$start = strtotime($_REQUEST['start']);
	$end = strtotime($_REQUEST['end']);
	$pines->com_reports->report_issues($start, $end, $location, $employee);
} else {
	// strtotime('next monday', time() - 604800) gets today if it's Monday, or the last Monday.
	$pines->com_reports->report_issues(strtotime('next monday', time() - 604800), time(), $_SESSION['user']->group);
}
?>