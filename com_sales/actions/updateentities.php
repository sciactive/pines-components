<?php
/**
 * Update the entities to the new OOP style classes.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'updateentities', null, false));
	return;
}

// POs
$array = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'po'), 'class' => com_sales_po));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->vendor = com_sales_vendor::factory($cur->vendor->guid);
	if (is_null($cur->vendor->guid))
		$cur->vendor = null;
	$cur->shipper = com_sales_shipper::factory($cur->shipper->guid);
	if (is_null($cur->shipper->guid))
		$cur->shipper = null;
	foreach ($cur->products as &$cur2) {
		$cur2['entity'] = com_sales_product::factory($cur2['entity']->guid);
		if (is_null($cur2['entity']->guid))
			$cur2['entity'] = null;
		if (is_array($cur2['stock_entities'])) {
			foreach ($cur2['stock_entities'] as $key => &$cur3) {
				$cur3 = com_sales_stock::factory($cur3->guid);
				if (is_null($cur3->guid))
					unset($cur2['stock_entities'][$key]);
			}
		}
		unset($cur3);
	}
	unset($cur2);
	$cur->save();
}

// Products
$array = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'product'), 'class' => com_sales_product));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->manufacturer = com_sales_manufacturer::factory($cur->manufacturer->guid);
	if (is_null($cur->manufacturer->guid))
		$cur->manufacturer = null;
	foreach ($cur->additional_tax_fees as $key => &$cur2) {
		$cur2 = com_sales_tax_fee::factory($cur2->guid);
		if (is_null($cur2->guid))
			unset($cur->additional_tax_fees[$key]);
	}
	foreach ($cur->vendors as &$cur2) {
		$cur2['entity'] = com_sales_vendor::factory($cur2['entity']->guid);
		if (is_null($cur2['entity']->guid))
			$cur2['entity'] = null;
	}
	unset($cur2);
	$cur->save();
}

// Sales
$array = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'sale'), 'class' => com_sales_sale));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->customer = com_sales_customer::factory($cur->customer->guid);
	if (is_null($cur->customer->guid))
		$cur->customer = null;
	foreach ($cur->products as &$cur2) {
		$cur2['entity'] = com_sales_product::factory($cur2['entity']->guid);
		if (is_null($cur2['entity']->guid))
			$cur2['entity'] = null;
	}
	unset($cur2);
	foreach ($cur->payments as &$cur2) {
		$cur2['entity'] = com_sales_payment_type::factory($cur2['entity']->guid);
		if (is_null($cur2['entity']->guid))
			$cur2['entity'] = null;
	}
	unset($cur2);
	$cur->save();
}

// Transfers
$array = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'transfer'), 'class' => com_sales_transfer));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->shipper = com_sales_shipper::factory($cur->shipper->guid);
	if (is_null($cur->shipper->guid))
		$cur->shipper = null;
	foreach ($cur->stock as $key => &$cur2) {
		$cur2 = com_sales_stock::factory($cur2->guid);
		if (is_null($cur2->guid))
			unset($cur->stock[$key]);
	}
	unset($cur2);
	$cur->save();
}

// Stock
$array = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->vendor = com_sales_vendor::factory($cur->vendor->guid);
	if (is_null($cur->vendor->guid))
		$cur->vendor = null;
	$cur->product = com_sales_product::factory($cur->product->guid);
	if (is_null($cur->product->guid))
		$cur->product = null;
	$cur->save();
}

// Transactions - payment
$array = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'transaction', 'payment_tx'), 'class' => com_sales_tx));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->ref = com_sales_payment_type::factory($cur->ref->guid);
	if (is_null($cur->ref->guid))
		$cur->ref = null;
	$cur->ticket = com_sales_sale::factory($cur->ticket->guid);
	if (is_null($cur->ticket->guid))
		$cur->ticket = null;
	$cur->save();
}

// Transactions - sale
$array = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'transaction', 'sale_tx'), 'class' => com_sales_tx));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->ticket = com_sales_sale::factory($cur->ticket->guid);
	if (is_null($cur->ticket->guid))
		$cur->ticket = null;
	$cur->save();
}

// Transactions - stock
$array = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'transaction', 'stock_tx'), 'class' => com_sales_tx));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->stock = com_sales_stock::factory($cur->stock->guid);
	if (is_null($cur->stock->guid))
		$cur->stock = null;
	if ($cur->ref->has_tag('po')) {
		$cur->ref = com_sales_po::factory($cur->ref->guid);
		if (is_null($cur->ref->guid))
			$cur->ref = null;
	} elseif ($cur->ref->has_tag('transfer')) {
		$cur->ref = com_sales_transfer::factory($cur->ref->guid);
		if (is_null($cur->ref->guid))
			$cur->ref = null;
	}
	$cur->save();
}

// Transactions - timer
$array = $config->entity_manager->get_entities(array('tags' => array('com_customer_timer', 'transaction'), 'class' => com_customer_timer_tx));
if (!is_array($array))
	$array = array();
foreach ($array as $cur) {
	$cur->customer = com_customer_customer::factory($cur->customer->guid);
	if (is_null($cur->customer->guid))
		$cur->customer = null;
	$cur->save();
}

?>