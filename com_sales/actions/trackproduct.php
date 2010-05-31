<?php
/**
 * List a product's history (invoices, POs, transfers, countsheets, etc).
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/trackproducts') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'trackproduct', $_REQUEST));

if (isset($_REQUEST['location'])) {
	$location = $_REQUEST['location'];
	if ($location != 'all')
		$location = group::factory((int) $location);
}
if ($_REQUEST['all_time'] == 'false') {
	$start = strtotime($_REQUEST['start_date']);
	$end = strtotime($_REQUEST['end_date']);
}
$pines->com_sales->track_product($_REQUEST['serial'], $_REQUEST['sku'], $location, $start, $end);

?>