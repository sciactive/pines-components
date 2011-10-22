<?php
/**
 * Save changes to a sale.
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

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editsale') )
		punt_user(null, pines_url('com_sales', 'sale/list'));
	$sale = com_sales_sale::factory((int) $_REQUEST['id']);
	if (!isset($sale->guid)) {
		pines_error('Requested sale id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newsale') )
		punt_user(null, pines_url('com_sales', 'sale/list'));
	$sale = com_sales_sale::factory();
}

if ($pines->config->com_sales->com_customer && $sale->status != 'invoiced' && $sale->status != 'paid' && $sale->status != 'voided') {
	$sale->customer = null;
	if (preg_match('/^\d+/', $_REQUEST['customer'])) {
		$sale->customer = com_customer_customer::factory((int) $_REQUEST['customer']);
		if (!isset($sale->customer->guid))
			$sale->customer = null;
	}
}
// Used for product error checking.
$product_error = false;
// Used to check products which allow only one per ticket.
$one_per_ticket_guids = array();
if ($sale->status != 'invoiced' && $sale->status != 'paid' && $sale->status != 'voided') {
	$sale->warehouse = false;
	$sale->products = (array) json_decode($_REQUEST['products']);
	if (empty($sale->products)) {
		pines_notice('No products were selected.');
		$product_error = true;
	} else {
		foreach ($sale->products as $key => &$cur_product) {
			$cur_product_entity = com_sales_product::factory((int) $cur_product->key);
			$cur_sku = $cur_product_entity->sku;
			$cur_serial = $cur_product->values[2];
			$cur_delivery = $cur_product->values[3];
			if (!in_array($cur_delivery, array('in-store', 'shipped', 'pick-up', 'warehouse')))
				$cur_delivery = 'in-store';
			$cur_qty = (int) $cur_product->values[4];
			$cur_price = (float) $cur_product->values[5];
			$cur_discount = $cur_product->values[6];
			$cur_esp = $cur_product->values[9];
			$cur_salesperson = null;
			if ($pines->config->com_sales->per_item_salesperson)
				$cur_salesperson = user::factory(intval($cur_product->values[10]));
			// Default to the sale's user.
			if (!isset($cur_salesperson->guid))
				$cur_salesperson = $sale->user->guid ? $sale->user : $_SESSION['user'];
			if ($cur_delivery == 'shipped' && !$sale->has_tag('shipping_shipped'))
				$sale->add_tag('shipping_pending');
			if (!isset($cur_product_entity->guid)) {
				pines_error("Product with id [$cur_product->key] and entered SKU [$cur_sku] was not found.");
				unset($sale->products[$key]);
				$product_error = true;
				continue;
			}
			if (!$cur_product_entity->enabled) {
				pines_error("Product with id [$cur_product->key] is not enabled.");
				unset($sale->products[$key]);
				$product_error = true;
				continue;
			}
			if ($cur_product_entity->serialized)
				$cur_qty = 1;
			$cur_product = array(
				'entity' => $cur_product_entity,
				'sku' => $cur_sku,
				'serial' => $cur_serial,
				'delivery' => $cur_delivery,
				'quantity' => $cur_qty,
				'price' => $cur_price,
				'discount' => $cur_discount,
				'salesperson' => $cur_salesperson
			);
			if ($pines->config->com_sales->com_esp)
				$cur_product['esp'] = $cur_esp;
			if ($cur_product_entity->serialized && empty($cur_serial) && $cur_delivery != 'warehouse') {
				pines_notice("Product with SKU [$cur_sku] requires a serial.");
				$product_error = true;
			}
			if ($cur_product_entity->stock_type != 'stock_optional' && $cur_delivery == 'warehouse') {
				pines_notice("Product with SKU [$cur_sku] is not stock optional, so it cannot be sold from warehouse.");
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
			if (!gatekeeper('com_sales/discountstock') && !empty($cur_discount)) {
				pines_notice('You don\'t have permission to discount items.');
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
			if ($cur_delivery == 'warehouse')
				$sale->warehouse = true;
		}
		unset($cur_product);
	}
}
// Used for payment error checking.
$payment_error = false;
if ($sale->status != 'paid' && $sale->status != 'voided') {
	$orig_payments = array();
	foreach ($sale->payments as $key => &$cur_payment) {
		if (!in_array($cur_payment['status'], array('approved', 'declined', 'tendered', 'voided'))) {
			$orig_payments[$key] = $cur_payment;
			unset($sale->payments[$key]);
		}
	}
	unset($cur_payment);
	$payments = (array) json_decode($_REQUEST['payments']);
	foreach ($payments as $cur_payment) {
		$cur_payment_type_entity = com_sales_payment_type::factory((int) $cur_payment->key);
		// Not used, but possibly in the future for logging purposes. (If the type is deleted.)
		$cur_type = $cur_payment->values[0];
		$cur_amount = (float) $cur_payment->values[1];
		$cur_status = $cur_payment->values[2];
		$data = $cur_payment->data;
		$orig_key = $cur_payment->orig_key;
		// TODO: Will this work on brand new sales? IE, one payment is info_request, and the other is approved, then they try to submit again.
		if (in_array($cur_status, array('approved', 'declined', 'tendered', 'voided')))
			continue;
		if (!isset($cur_payment_type_entity->guid)) {
			pines_error("Payment type with id [$cur_payment->key] was not found.");
			$payment_error = true;
			continue;
		}
		if (!$cur_payment_type_entity->enabled) {
			pines_error("Payment type with id [$cur_payment->key] is not enabled.");
			$payment_error = true;
			continue;
		}
		if ($cur_amount <= 0) {
			pines_notice('A payment was entered without an amount.');
			$payment_error = true;
		}
		$data_array = array();
		if (is_array($data->data)) {
			foreach ($data->data as $cur_data) {
				$data_array[$cur_data->name] = $cur_data->value;
			}
		}
		if (isset($orig_key) && isset($orig_payments[$orig_key])) {
			$orig_payments[$orig_key]['entity'] = $cur_payment_type_entity;
			$orig_payments[$orig_key]['type'] = $cur_type;
			$orig_payments[$orig_key]['amount'] = $cur_amount;
			$orig_payments[$orig_key]['status'] = $cur_status;
			$orig_payments[$orig_key]['data'] = $data_array;
			$sale->payments[] = $orig_payments[$orig_key];
			unset($orig_payments[$orig_key]);
		} else {
			$sale->payments[] = array(
				'entity' => $cur_payment_type_entity,
				'type' => $cur_type,
				'amount' => $cur_amount,
				'status' => $cur_status,
				'data' => $data_array
			);
		}
	}
}
if ($pines->config->com_sales->com_customer && $_REQUEST['shipping_use_customer'] == 'ON') {
	$sale->shipping_use_customer = true;
	$sale->shipping_address = (object) array(
		'name' => $sale->customer->name,
		'address_type' => $sale->customer->address_type,
		'address_1' => $sale->customer->address_1,
		'address_2' => $sale->customer->address_2,
		'city' => $sale->customer->city,
		'state' => $sale->customer->state,
		'zip' => $sale->customer->zip,
		'address_international' => $sale->customer->address_international
	);
} else {
	$sale->shipping_use_customer = false;
	$sale->shipping_address = (object) array(
		'name' => $_REQUEST['shipping_name'],
		'address_type' => $_REQUEST['shipping_address_type'] == 'international' ? 'international' : 'us',
		'address_1' => $_REQUEST['shipping_address_1'],
		'address_2' => $_REQUEST['shipping_address_2'],
		'city' => $_REQUEST['shipping_city'],
		'state' => $_REQUEST['shipping_state'],
		'zip' => $_REQUEST['shipping_zip'],
		'address_international' => $_REQUEST['shipping_address_international']
	);
}
$sale->comments = $_REQUEST['comments'];

if ($product_error || $payment_error) {
	$sale->print_form();
	return;
}

if ($pines->config->com_sales->global_sales)
	$sale->ac->other = 1;

if ($_REQUEST['process'] == 'invoice' && $sale->status != 'invoiced' && $sale->status != 'paid' && $sale->status != 'voided' && $pines->config->com_sales->allow_invoicing) {
	if (!$sale->invoice()) {
		$sale->print_form();
		pines_error('There was a problem while invoicing the sale. Please check that all information is correct and resubmit.');
		return;
	}
}

if ($_REQUEST['process'] == 'tender' && $sale->status != 'paid' && $sale->status != 'voided') {
	if (!$sale->complete()) {
		$sale->print_form();
		pines_error('There was a problem while completing the sale. It has not been completed yet. Please check that all information is correct and resubmit.');
		return;
	}
}

if (!isset($sale->status) || $sale->status == 'quoted') {
	if ($sale->status == 'quoted' && !$pines->config->com_sales->allow_quoting) {
		$sale->print_form();
		pines_notice('Quoting sales is not allowed.');
		return;
	}
	$sale->status = 'quoted';
	$sale->total();
}

if ($sale->save()) {
	pines_notice('Saved sale ['.$sale->id.']');
	pines_redirect(pines_url('com_sales', 'sale/receipt', array('id' => $sale->guid, 'autoprint' => 'ok')));
} else {
	$sale->print_form();
	pines_error('Error saving sale. Do you have permission?');
	return;
}

?>