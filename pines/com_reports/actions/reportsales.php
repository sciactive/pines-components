<?php
/**
 * List sales for a sales report.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/listsales') )
	punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'reportsales', null, false));
	
if ( isset($_REQUEST['report_start']) ) {
	$start = $_REQUEST['report_start'];
	$end = $_REQUEST['report_end'];
	$pines->com_reports->report_sales($start, $end);
} else {
	$pines->com_reports->report_sales(date('n/j/Y', strtotime('now')), date('n/j/Y', strtotime('now')));
}
?>