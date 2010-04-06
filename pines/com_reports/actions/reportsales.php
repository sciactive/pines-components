<?php
/**
 * List sales for a sales report.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportsales') )
	punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'reportsales'));
	
if ( isset($_REQUEST['start']) ) {
	$start = strtotime($_REQUEST['start']);
	$end = strtotime($_REQUEST['end']);
	$pines->com_reports->report_sales($start, $end);
} else {
	$pines->com_reports->report_sales(strtotime('next monday', time() - 604800), time());
}
?>