<?php
/**
 * Search products, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/listproducts') && !gatekeeper('com_sales/newsale') && !gatekeeper('com_sales/editsale'))
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'productsearch', $_REQUEST, false));

$config->page->override = true;

$code = $_REQUEST['code'];

if (empty($code)) {
	$product = null;
} elseif(!$_REQUEST['useguid']) {
	$product = $config->run_sales->get_product_by_code($code);
	if (!$product->enabled) {
		$product = null;
	}
} else {
	$product = com_sales_product::factory((int) $code);
	if (is_null($product->guid) || !$product->enabled) {
		$product = null;
	}
}

if (!is_null($product)) {
	$fees_percent = array();
	$fees_flat = array();
	foreach ($product->additional_tax_fees as $cur_tax_fee) {
		if (!$cur_tax_fee->enabled)
			continue;
		if ($cur_tax_fee->type == 'percentage') {
			$fees_percent[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
		} elseif ($cur_tax_fee->type == 'flat_rate') {
			$fees_flat[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
		}
	}
	
	$json_struct = (object) array(
		'guid' => $product->guid,
		'name' => $product->name,
		'sku' => $product->sku,
		'pricing_method' => $product->pricing_method,
		'unit_price' => $product->unit_price,
		'margin' => $product->margin,
		'floor' => $product->floor,
		'tax_exempt' => $product->tax_exempt,
		'serialized' => $product->serialized,
		'discountable' => $product->discountable,
		'require_customer' => $product->require_customer,
		'non_refundable' => $product->non_refundable,
		'fees_percent' => $fees_percent,
		'fees_flat' => $fees_flat
	);

	$product = $json_struct;
}

$config->page->override_doc(json_encode($product));

?>