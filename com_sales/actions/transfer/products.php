<?php
/**
 * Search transfers, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') && !gatekeeper('com_sales/receive'))
	punt_user(null, pines_url('com_sales', 'transfer/products', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$transfer = com_sales_transfer::factory((int) $_REQUEST['id']);
if (!isset($transfer->guid))
	return;

$products = $transfer->pending_products;
foreach ($products as $key => &$cur_product) {
	$json_struct = (object) array(
		'guid'			=> $cur_product->guid,
		'name'			=> $cur_product->name,
		'sku'			=> $cur_product->sku,
		'serialized'	=> $cur_product->serialized,
		'quantity'		=> 1
	);
	$cur_product = $json_struct;
}
unset($cur_product);

if (empty($products))
	$products = null;

$pines->page->override_doc(json_encode($products));

?>