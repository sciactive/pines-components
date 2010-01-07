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
	$sale = com_sales_sale::factory((int) $_REQUEST['id']);
	if (is_null($sale->guid)) {
		display_error('Requested sale id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newsale') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listsales', null, false));
		return;
	}
	$sale = com_sales_sale::factory();
}

if ($sale->status != 'invoiced' && $sale->status != 'paid') {
	$sale->customer = $_REQUEST['customer'];
	if (preg_match('/^\d+/', $sale->customer)) {
		$sale->customer = com_sales_customer::factory(intval($sale->customer));
		if (is_null($sale->customer->guid))
			$sale->customer = null;
	} else {
		$sale->customer = null;
	}
}
// Used for product error checking.
$product_error = false;
if ($sale->status != 'invoiced' && $sale->status != 'paid') {
	$sale->products = json_decode($_REQUEST['products']);
	if (!is_array($sale->products))
		$sale->products = array();
	if (empty($sale->products)) {
		display_notice("No products were selected.");
		$product_error = true;
	} else {
		foreach ($sale->products as $key => &$cur_product) {
			$cur_product_entity = com_sales_product::factory(intval($cur_product->key));
			$cur_sku = $cur_product->values[0];
			$cur_serial = $cur_product->values[2];
			$cur_delivery = $cur_product->values[3];
			if (!in_array($cur_delivery, array('in-store', 'shipped')))
				$cur_delivery = 'in-store';
			$cur_qty = intval($cur_product->values[4]);
			$cur_price = floatval($cur_product->values[5]);
			$cur_discount = $cur_product->values[6];
			if (is_null($cur_product_entity->guid)) {
				display_error("Product with id [$cur_product->key] and entered SKU [$cur_sku] was not found.");
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
			$cur_product = array(
				'entity' => $cur_product_entity,
				'sku' => $cur_sku,
				'serial' => $cur_serial,
				'delivery' => $cur_delivery,
				'quantity' => $cur_qty,
				'price' => $cur_price,
				'discount' => $cur_discount
			);
		}
		unset($cur_product);
	}
}
// Used for payment error checking.
$payment_error = false;
if ($sale->status != 'paid') {
	$sale->payments = json_decode($_REQUEST['payments']);
	if (!is_array($sale->payments))
		$sale->payments = array();
	foreach ($sale->payments as $key => &$cur_payment) {
		$cur_payment_type_entity = com_sales_payment_type::factory(intval($cur_payment->key));
		// Not used, but possibly in the future for logging purposes. (If the type is deleted.)
		$cur_type = $cur_payment->values[0];
		$cur_amount = floatval($cur_payment->values[1]);
		if (is_null($cur_payment_type_entity->guid)) {
			display_error("Payment type with id [$cur_payment->key] was not found.");
			unset($sale->payments[$key]);
			$payment_error = true;
			continue;
		}
		if ($cur_amount <= 0) {
			display_notice("A payment was entered without an amount.");
			$payment_error = true;
		}
		if ($cur_amount < $cur_payment_type_entity->minimum) {
			display_notice("The payment type [$cur_type] requires a minimum payment of {$cur_payment_type_entity->minimum}.");
			$payment_error = true;
		}
		$cur_payment = array(
			'entity' => $cur_payment_type_entity,
			'type' => $cur_type,
			'amount' => $cur_amount
		);
	}
	unset($cur_payment);
}
$sale->comments = $_REQUEST['comments'];

if ($product_error || $payment_error) {
	$sale->print_form();
	return;
}

if ($config->com_sales->global_sales) {
	$sale->ac = (object) array('other' => 1);
}

if (($_REQUEST['process'] == 'Invoice' || $_REQUEST['process'] == 'Complete') && $sale->status != 'invoiced' && $sale->status != 'paid') {
	if (!$sale->invoice()) {
		$sale->print_form();
		display_error('There was an error while invoicing the sale. Please check that all information is correct and resubmit.');
		return;
	}
}

if ($_REQUEST['process'] == 'Complete') {
	if (!$sale->complete()) {
		$sale->print_form();
		display_error('There was an error while completing the sale. It has been invoiced, but not completed yet. Please check that all information is correct and resubmit.');
		return;
	}
}

if (!isset($sale->status) || $sale->status == 'quoted') {
	$sale->status = 'quoted';
	$sale->total();
}

if ($sale->save()) {
	display_notice('Saved sale ['.$sale->guid.']');
} else {
	$sale->print_form();
	display_error('Error saving sale. Do you have permission?');
	return;
}

$sale->print_receipt();
?>