<?php
/**
 * List product transactions for a product details report.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportproducts') )
	punt_user(null, pines_url('com_reports', 'reportproducts'));
	
if ( isset($_REQUEST['start']) ) {
	$start = strtotime($_REQUEST['start']);
	$end = strtotime($_REQUEST['end']);
} else {
	$start = strtotime('next monday', time() - 604800);
	$end = time();
}
if ( isset($_REQUEST['location']) )
	$location = group::factory((int) $_REQUEST['location']);

$pines->com_reports->report_product_details($start, $end, $location);
?>