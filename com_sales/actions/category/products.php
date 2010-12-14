<?php
/**
 * Get products in a category, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/listproducts') && !gatekeeper('com_sales/newsale') && !gatekeeper('com_sales/editsale'))
	punt_user(null, pines_url('com_sales', 'category/products', $_REQUEST));

$pines->page->override = true;

$category = com_sales_category::factory((int) $_REQUEST['id']);

if (!isset($category->guid)) {
	$pines->page->override_doc(json_encode(array()));
	return;
}

$return = array();
foreach ($category->products as $product) {
	if (!$product->enabled)
		continue;
	$fees_percent = array();
	$fees_flat = array();
	if ($product->additional_tax_fees) {
		foreach ($product->additional_tax_fees as $cur_tax_fee) {
			if (!$cur_tax_fee->enabled)
				continue;
			if ($cur_tax_fee->type == 'percentage') {
				$fees_percent[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
			} elseif ($cur_tax_fee->type == 'flat_rate') {
				$fees_flat[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
			}
		}
	}
	
	$json_struct = (object) array(
		'guid' => $product->guid,
		'name' => $product->name,
		'sku' => $product->sku,
		'stock_type' => $product->stock_type,
		'pricing_method' => $product->pricing_method,
		'unit_price' => $product->unit_price,
		'margin' => $product->margin,
		'floor' => $product->floor,
		'ceiling' => $product->ceiling,
		'tax_exempt' => $product->tax_exempt,
		'serialized' => $product->serialized,
		'discountable' => $product->discountable,
		'require_customer' => $product->require_customer,
		'one_per_ticket' => $product->one_per_ticket,
		'non_refundable' => $product->non_refundable,
		'fees_percent' => $fees_percent,
		'fees_flat' => $fees_flat
	);

	$return[] = $json_struct;
}

$pines->page->override_doc(json_encode($return));

?>