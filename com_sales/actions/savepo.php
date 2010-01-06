<?php
/**
 * Save changes to a PO.
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
	if ( !gatekeeper('com_sales/editpo') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listpos', null, false));
		return;
	}
	$po = com_sales_po::factory((int) $_REQUEST['id']);
	if (!isset($po->guid)) {
		display_error('Requested PO id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newpo') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listpos', null, false));
		return;
	}
	$po = com_sales_po::factory();
}

// General
$po->po_number = $_REQUEST['po_number'];
$po->reference_number = $_REQUEST['reference_number'];
// Vendor can't be changed after items have been received.
if (empty($po->received)) {
	$po->vendor = com_sales_vendor::factory(intval($_REQUEST['vendor']));
	if (!isset($po->vendor->guid))
		$po->vendor = null;
}
// Destination can't be changed after items have been received.
if (empty($po->received))
	$po->destination = $config->user_manager->get_group(intval($_REQUEST['destination']));
$po->shipper = com_sales_shipper::factory(intval($_REQUEST['shipper']));
if (!isset($po->shipper->guid))
	$po->shipper = null;
$po->eta = strtotime($_REQUEST['eta']);

// Products
// Products can't be changed after items have been received.
if (empty($po->received)) {
	$po->products = json_decode($_REQUEST['products']);
	if (!is_array($po->products))
		$po->products = array();
	foreach ($po->products as &$cur_product) {
		$cur_product = array(
			'entity' => com_sales_product::factory(intval($cur_product->key)),
			'quantity' => intval($cur_product->values[2]),
			'cost' => floatval($cur_product->values[3])
		);
		if (!isset($cur_product['entity']->guid))
			$cur_product['entity'] = null;
	}
	unset($cur_product);
}

if (empty($po->po_number)) {
	$po->print_form();
	display_notice('Please specify a PO number.');
	return;
}
$test = $config->entity_manager->get_entities_by_data(array('po_number' => $po->po_number), array('com_sales', 'po'), false, com_sales_po);
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
	$po->print_form();
	display_notice('There is already a PO with that number. Please enter a different number.');
	return;
}
if (is_null($po->vendor)) {
	$po->print_form();
	display_error('Specified vendor is not valid.');
	return;
}
if (is_null($po->shipper)) {
	$po->print_form();
	display_error('Specified shipper is not valid.');
	return;
}

$po->ac = (object) array('other' => 2);
if ($po->save()) {
	display_notice('Saved PO ['.$po->po_number.']');
} else {
	display_error('Error saving PO. Do you have permission?');
}

$config->run_sales->list_pos();
?>