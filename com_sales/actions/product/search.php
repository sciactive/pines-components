<?php
/**
 * Search products, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/searchproducts'))
	punt_user(null, pines_url('com_sales', 'product/search', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$code = $_REQUEST['code'];

if (empty($code)) {
	$product = null;
} elseif(!$_REQUEST['useguid']) {
	$product = $pines->com_sales->get_product_by_code($code);
	if (!$product->enabled)
		$product = null;
} else {
	$product = com_sales_product::factory((int) $code);
	if (!isset($product->guid) || !$product->enabled)
		$product = null;
}

if (isset($product)) {
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
		'stock_type' => $product->stock_type,
		'pricing_method' => $product->pricing_method,
		'unit_price' => $product->unit_price,
		'margin' => $product->margin,
		'floor' => $product->floor,
		'ceiling' => $product->ceiling,
		'tax_exempt' => $product->tax_exempt,
		'return_checklists' => array(),
		'serialized' => $product->serialized,
		'discountable' => $product->discountable,
		'require_customer' => $product->require_customer,
		'one_per_ticket' => $product->one_per_ticket,
		'non_refundable' => $product->non_refundable,
		'fees_percent' => $fees_percent,
		'fees_flat' => $fees_flat,
		'serials' => array()
	);

	foreach ((array) $product->return_checklists as $cur_return_checklist) {
		if (!$cur_return_checklist->enabled)
			continue;
		$json_struct->return_checklists[] = array('guid' => $cur_return_checklist->guid, 'label' => $cur_return_checklist->label, 'conditions' => (array) $cur_return_checklist->conditions);
	}

	// Look up serials in the user's current location to allow them to choose.
	if ($product->serialized && $pines->config->com_sales->add_product_show_serials) {
		$selector = array('&',
				'tag' => array('com_sales', 'stock'),
				'data' => array('available', true),
				'ref' => array(
					array('product', $product)
				)
			);
		if (isset($_SESSION['user']->group->guid))
			$selector['ref'][] = array('location', $_SESSION['user']->group);
		$stock_entries = $pines->entity_manager->get_entities(
				array('class' => com_sales_stock, 'limit' => $pines->config->com_sales->add_product_show_serials),
				$selector
			);
		foreach ($stock_entries as $cur_stock) {
			$json_struct->serials[] = $cur_stock->serial;
		}
	}

	$product = $json_struct;
}

$pines->page->override_doc(json_encode($product));

?>