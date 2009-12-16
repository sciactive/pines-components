<?php
/**
 * Save changes to a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editsale') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listsales', null, false));
		return;
	}
	$sale = $config->run_sales->get_sale($_REQUEST['id']);
	if (is_null($sale)) {
		display_error('Requested sale id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newsale') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listsales', null, false));
		return;
	}
	$sale = new com_sales_sale;
}

$sale->customer = $_REQUEST['customer'];
if (preg_match('/^\d+/', $sale->customer)) {
	$sale->customer = $config->run_sales->get_customer(intval($sale->customer));
} else {
	$sale->customer = null;
}
$sale->delivery_method = $_REQUEST['delivery_method'];
if (!in_array($sale->delivery_method, array('in-store', 'shipped')))
	$sale->delivery_method = 'in-store';
// Used for product error checking.
$product_error = false;
$sale->products = json_decode($_REQUEST['products']);
if (!is_array($sale->products))
	$sale->products = array();
// These will be searched through to match products to stock entries.
$stock_entries = $config->entity_manager->get_entities_by_tags('com_sales', 'stock', com_sales_stock);
if (empty($stock_entries))
	$stock_entries = array();
foreach ($sale->products as $key => &$cur_product) {
	// TODO: Save fees, calculate total.
	$cur_product_entity = $config->run_sales->get_product(intval($cur_product->key));
	$cur_sku = $cur_product->values[0];
	$cur_serial = $cur_product->values[2];
	$cur_qty = intval($cur_product->values[3]);
	$cur_price = floatval($cur_product->values[4]);
	$cur_discount = $cur_product->values[5];
	if (is_null($cur_product_entity)) {
		display_notice("Product with id [$cur_product->key] and entered SKU [$cur_sku] was not found.");
		unset($sale->products[$key]);
		$product_error = true;
		continue;
	}
	if ($cur_product_entity->serialized)
		$cur_qty = 1;
	if ($cur_product_entity->serialized && empty($cur_serial)) {
		display_notice("Product with SKU [$cur_sku] requires a serial.");
		$product_error = true;
	}
	if ($cur_qty < 1) {
		display_notice("Product with SKU [$cur_sku] has a zero or negative quantity.");
		$product_error = true;
	}
	if ($cur_product_entity->unit_price != $cur_price) {
		display_notice("Product with SKU [$cur_sku] has an incorrect price.");
		$product_error = true;
	}
	if (!$cur_product_entity->discountable && !empty($cur_discount)) {
		display_notice("Product with SKU [$cur_sku] is not discountable.");
		$product_error = true;
	}
	// Find the stock entry.
	// TODO: Non-stocked or stock optional items.
	// TODO: Ship to customer from different stock (Warehouse).
	$stock_entities = array();
	for ($i = 0; $i < $cur_qty; $i++) {
		$found = false;
		foreach($stock_entries as $key => &$cur_stock) {
			if (($cur_stock->status != 'available') ||
				(!$_SESSION['user']->ingroup($cur_stock->location->guid)) ||
				($cur_stock->product != $cur_product_entity) ||
				($cur_product_entity->serialized && ($cur_serial != $cur_stock->serial))) {
				continue;
			}
			// One was found, so save it then take it out of our search stock.
			$found = true;
			$stock_entities[] = clone $cur_stock;
			unset($stock_entries[$key]);
			break;
		}
		if (!$found) {
			// It wasn't found.
			display_notice("Product with SKU [$cur_sku]".($cur_product_entity->serialized ? " and serial [$cur_serial]" : " and quantity $cur_qty")." is not in local stock.".($cur_product_entity->serialized && count($stock_entities) ? '' : ' Found '.count($stock_entities).'.'));
			$product_error = true;
			break;
		}
	}
	$cur_product = array(
		'entity' => $cur_product_entity,
		'stock_entities' => $stock_entities,
		'sku' => $cur_sku,
		'serial' => $cur_serial,
		'quantity' => $cur_qty,
		'price' => $cur_price,
		'discount' => $cur_discount
	);
}
unset($cur_product);
// Used for payment error checking.
$payment_error = false;
$sale->payments = json_decode($_REQUEST['payments']);
if (!is_array($sale->payments))
	$sale->payments = array();
foreach ($sale->payments as $key => &$cur_payment) {
	// TODO: Calculate tendered.
	$cur_payment_entity = $config->run_sales->get_payment_type(intval($cur_payment->key));
	// Not used, but possibly in the future for logging purposes. (If the type is deleted.)
	$cur_type = $cur_payment->values[0];
	$cur_amount = floatval($cur_payment->values[1]);
	if (is_null($cur_payment_entity)) {
		display_notice("Payment type with id [$cur_payment->key] was not found.");
		unset($sale->payments[$key]);
		$payment_error = true;
		continue;
	}
	if ($cur_amount <= 0) {
		display_notice("A payment was entered without an amount.");
		$payment_error = true;
	}
	$cur_payment = array(
		'entity' => $cur_payment_entity,
		'type' => $cur_type,
		'amount' => $cur_amount
	);
}
unset($cur_payment);
$sale->comments = $_REQUEST['comments'];

if ($product_error) {
	$module = $config->run_sales->print_sale_form('com_sales', 'savesale');
	$module->entity = $sale;
	display_error('There were product errors. Please check that product details are correct and resubmit.');
	return;
}
if ($payment_error) {
	$module = $config->run_sales->print_sale_form('com_sales', 'savesale');
	$module->entity = $sale;
	display_error('There were payment errors. Please check that payment details are correct and resubmit.');
	return;
}

$module = $config->run_sales->print_sale_form('com_sales', 'savesale');
$module->entity = $sale;
return;

if ($config->com_sales->global_sales) {
	$sale->ac = (object) array('other' => 1);
}
if ($sale->save()) {
	display_notice('Saved sale ['.$sale->guid.']');
} else {
	display_error('Error saving sale. Do you have permission?');
}

$config->run_sales->list_sales();
?>