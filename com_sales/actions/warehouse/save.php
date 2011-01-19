<?php
/**
 * Save changes to a warehouse sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/warehouse') )
	punt_user(null, pines_url('com_sales', 'warehouse/fulfill'));

$sale = com_sales_sale::factory((int) $_REQUEST['id']);
if (!isset($sale->guid)) {
	pines_error('Requested sale id is not accessible.');
	return;
}
if ($sale->status != 'invoiced' && $sale->status != 'paid') {
	pines_error('Requested sale has not been invoiced.');
	return;
}
if (!$sale->warehouse_items) {
	pines_error('Requested sale has no warehouse items.');
	return;
}

$stock_entries = (array) json_decode($_REQUEST['products'], true);

$guids = array();
foreach ($stock_entries as $cur_entry) {
	$cur_location = group::factory((int) $cur_entry['location']);
	if (!isset($cur_location->guid)) {
		pines_notice('Invalid location provided. Item is being skipped.');
		continue;
	}
	$cur_product = com_sales_product::factory((int) $cur_entry['product']);
	if (!isset($cur_location->guid)) {
		pines_notice('Invalid product provided. Item is being skipped.');
		continue;
	}
	// Should we just use location GUID directly from POST?
	$selector = array('&',
			'tag' => array('com_sales', 'stock'),
			'data' => array(
				array('available', true)
			),
			'ref' => array(
				array('location', $cur_location),
				array('product', $cur_product)
			)
		);
	if ($cur_product->serialized) {
		if (empty($cur_entry['serial'])) {
			pines_notice('No serial provided. Item is being skipped.');
			continue;
		}
		$selector['data'][] = array('serial', $cur_entry['serial']);
	}
	$stock = $pines->entity_manager->get_entity(
			array('class' => com_sales_stock),
			$selector,
			array('!&',
				'guid' => $guids
			)
		);
	if (!isset($stock->guid)) {
		pines_notice('No stock was found that matched this query. Item is being skipped.');
		continue;
	}
	$key = (int) $cur_entry['key'];
	// Check the product.
	if (!isset($sale->products[$key]) || !$sale->products[$key]['entity']->is($cur_product)) {
		pines_notice('Stock doesn\'t match product on the sale. Item is being skipped.');
		continue;
	}
	// Calculate quantity and fulfilled.
	$quantity = $sale->products[$key]['quantity'] - (int) $sale->products[$key]['returned_quantity'];
	$fulfilled = count($sale->products[$key]['stock_entities']) - count((array) $sale->products[$key]['returned_stock_entities']);
	// Check the quantity.
	if ($quantity <= $fulfilled) {
		pines_notice('This product is already fulfilled. Item is being skipped.');
		continue;
	}
	// Check that the product is warehouse.
	if ($sale->products[$key]['delivery'] != 'warehouse') {
		pines_notice('This product is not a warehouse item. Item is being skipped.');
		continue;
	}
	// Removed the stock.
	if (!($stock->remove('sold_pending_shipping', $sale, $stock->location) && $stock->save())) {
		pines_notice('Stock could not be removed. Item is being skipped.');
		continue;
	}
	// Add it to the sale.
	$sale->products[$key]['stock_entities'][] = $stock;
	if ($cur_product->serialized)
		$sale->products[$key]['serial'] = $stock->serial;
	// Save the sale, just to be safe.
	if (!$sale->save()) {
		pines_error('Sale could not be saved.');
		$sale->print_warehouse();
		return;
	}
}

// Convert fulfilled warehouse products to shipped.
$sale->warehouse_complete = true;
foreach ($sale->products as &$cur_product) {
	if ($cur_product['delivery'] != 'warehouse')
		continue;
	// Calculate quantity and fulfilled.
	$quantity = $cur_product['quantity'] - (int) $cur_product['returned_quantity'];
	$fulfilled = count($cur_product['stock_entities']) - count((array) $cur_product['returned_stock_entities']);
	if ($quantity <= $fulfilled) {
		$cur_product['delivery'] = 'shipped';
		$cur_product['was_warehouse'] = true;
		// There are shippable items now.
		$sale->add_tag('shipping_pending');
	} else {
		$sale->warehouse_complete = false;
	}
}
unset($cur_product);

if ($sale->save()) {
	if ($sale->warehouse_complete) {
		pines_notice('Fulfilled sale ['.$sale->id.']. It can now be completely shipped.');
		redirect(pines_url('com_sales', 'stock/shipments'));
	} else {
		pines_notice('Partially fulfilled sale ['.$sale->id.']. Fulfilled products can now be shipped.');
		redirect(pines_url('com_sales', 'warehouse/fulfill'));
	}
} else {
	$sale->print_warehouse();
	pines_error('Error saving sale. Do you have permission?');
	return;
}

?>