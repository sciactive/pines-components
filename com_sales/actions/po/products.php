<?php
/**
 * Search POs, returning JSON.
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

if ( !gatekeeper('com_sales/listpos') && !gatekeeper('com_sales/receive'))
	punt_user(null, pines_url('com_sales', 'po/products', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$po = com_sales_po::factory((int) $_REQUEST['id']);
if (!isset($po->guid))
	return;

$products = $po->products;
foreach ($products as $key => &$cur_product) {
	$json_struct = (object) array(
		'guid'			=> $cur_product['entity']->guid,
		'name'			=> $cur_product['entity']->name,
		'sku'			=> $cur_product['entity']->sku,
		'serialized'	=> $cur_product['entity']->serialized,
		'quantity'		=> $cur_product['quantity']
	);
	$cur_product = $json_struct;
}
unset($cur_product);

if (empty($products))
	$products = null;

$pines->page->override_doc(json_encode($products));

?>