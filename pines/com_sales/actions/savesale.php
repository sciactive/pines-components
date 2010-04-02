<?php
/**
 * Save changes to a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editsale') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listsales', null, false));
	$sale = com_sales_sale::factory((int) $_REQUEST['id']);
	if (is_null($sale->guid)) {
		pines_error('Requested sale id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newsale') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listsales', null, false));
	$sale = com_sales_sale::factory();
}

if ($pines->com_sales->com_customer && $sale->status != 'invoiced' && $sale->status != 'paid') {
	$sale->customer = null;
	if (preg_match('/^\d+/', $_REQUEST['customer'])) {
		$sale->customer = com_customer_customer::factory(intval($_REQUEST['customer']));
		if (is_null($sale->customer->guid))
			$sale->customer = null;
	}
}
// Used for product error checking.
$product_error = false;
// Used to check products which allow only one per ticket.
$one_per_ticket_guids = array();
if ($sale->status != 'invoiced' && $sale->status != 'paid') {
	$sale->products = (array) json_decode($_REQUEST['products']);
	if (empty($sale->products)) {
		pines_notice("No products were selected.");
		$product_error = true;
	} else {
		foreach ($sale->products as $key => &$cur_product) {
			$cur_product_entity = com_sales_product::factory(intval($cur_product->key));
			$cur_sku = $cur_product_entity->sku;
			$cur_serial = $cur_product->values[2];
			$cur_delivery = $cur_product->values[3];
			if (!in_array($cur_delivery, array('in-store', 'shipped')))
				$cur_delivery = 'in-store';
			$cur_qty = intval($cur_product->values[4]);
			$cur_price = floatval($cur_product->values[5]);
			$cur_discount = $cur_product->values[6];
			if (is_null($cur_product_entity->guid)) {
				pines_error("Product with id [$cur_product->key] and entered SKU [$cur_sku] was not found.");
				unset($sale->products[$key]);
				$product_error = true;
				continue;
			}
			if ($cur_product_entity->serialized)
				$cur_qty = 1;
			if ($cur_product_entity->serialized && empty($cur_serial)) {
				pines_notice("Product with SKU [$cur_sku] requires a serial.");
				$product_error = true;
			}
			if ($cur_qty < 1) {
				pines_notice("Product with SKU [$cur_sku] has a zero or negative quantity.");
				$product_error = true;
			}
			if ($cur_product_entity->pricing_method != 'variable' && $cur_product_entity->unit_price != $cur_price) {
				pines_notice("Product with SKU [$cur_sku] has an incorrect price.");
				$product_error = true;
			}
			if (!$cur_product_entity->discountable && !empty($cur_discount)) {
				pines_notice("Product with SKU [$cur_sku] is not discountable.");
				$product_error = true;
			}
			if ($cur_product_entity->one_per_ticket) {
				// This causes products with >1 qtys to not be added to $one_per_ticket_guids,
				// but that's ok, since they're already an erroneous entry.
				if ($cur_qty > 1 || in_array($cur_product_entity->guid, $one_per_ticket_guids)) {
					pines_notice("Only one of product with SKU [$cur_sku] is allowed on a ticket.");
					$product_error = true;
				} else {
					$one_per_ticket_guids[] = $cur_product_entity->guid;
				}
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
	foreach ($sale->payments as $key => $cur_payment) {
		if (!in_array($cur_payment['status'], array('approved', 'declined', 'tendered')))
			unset($sale->payments[$key]);
	}
	$payments = (array) json_decode($_REQUEST['payments']);
	foreach ($payments as $cur_payment) {
		$cur_payment_type_entity = com_sales_payment_type::factory((int) $cur_payment->key);
		// Not used, but possibly in the future for logging purposes. (If the type is deleted.)
		$cur_type = $cur_payment->values[0];
		$cur_amount = floatval($cur_payment->values[1]);
		$cur_status = $cur_payment->values[2];
		$data = $cur_payment->data;
		if (in_array($cur_status, array('approved', 'declined', 'tendered')))
			continue;
		if (is_null($cur_payment_type_entity->guid)) {
			pines_error("Payment type with id [$cur_payment->key] was not found.");
			$payment_error = true;
			continue;
		}
		if ($cur_amount <= 0) {
			pines_notice("A payment was entered without an amount.");
			$payment_error = true;
		}
		$data_array = array();
		if (is_array($data->data)) {
			foreach ($data->data as $cur_data) {
				$data_array[$cur_data->name] = $cur_data->value;
			}
		}
		$sale->payments[] = array(
			'entity' => $cur_payment_type_entity,
			'type' => $cur_type,
			'amount' => $cur_amount,
			'status' => $cur_status,
			'data' => $data_array
		);
	}
}
$sale->comments = $_REQUEST['comment_saver'];

if ($product_error || $payment_error) {
	$sale->print_form();
	return;
}

if ($pines->config->com_sales->global_sales)
	$sale->ac->other = 1;

if (($_REQUEST['process'] == 'invoice' || $_REQUEST['process'] == 'tender') && $sale->status != 'invoiced' && $sale->status != 'paid') {
	if (!$sale->invoice()) {
		$sale->print_form();
		pines_error('There was an error while invoicing the sale. Please check that all information is correct and resubmit.');
		return;
	}
}

if ($_REQUEST['process'] == 'tender') {
	if (!$sale->complete()) {
		$sale->save();
		$sale->print_form();
		pines_error('There was an error while completing the sale. It has been invoiced, but not completed yet. Please check that all information is correct and resubmit.');
		return;
	}
}

if (!isset($sale->status) || $sale->status == 'quoted') {
	$sale->status = 'quoted';
	$sale->total();
}

if ($sale->save()) {
	pines_notice('Saved sale ['.$sale->guid.']');
} else {
	$sale->print_form();
	pines_error('Error saving sale. Do you have permission?');
	return;
}

$sale->print_receipt();
?>
