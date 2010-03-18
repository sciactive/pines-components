<?php
/**
 * List employee info for a time and attendance report.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportattendance') )
	punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'reportattendance', null, false));
	
if ( isset($_REQUEST['start']) ) {
	$user = $_REQUEST['user'];
	$location = group::factory((int) $_REQUEST['location']);
	if (!isset($location->guid))
		unset($location);
	$start = strtotime($_REQUEST['start']);
	$end = strtotime($_REQUEST['end']);
	$pines->com_reports->report_attendance($start, $end, $location, $user);
} else {
	$days = date('N')-1;
	$pines->com_reports->report_attendance(strtotime('-'.$days.' days'), time());
}
?>