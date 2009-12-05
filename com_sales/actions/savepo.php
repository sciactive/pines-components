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
	$po = $config->run_sales->get_po($_REQUEST['id']);
	if (is_null($po)) {
		display_error('Requested PO id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newpo') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listpos', null, false));
		return;
	}
	$po = new entity('com_sales', 'po');
}

// General
$po->po_number = $_REQUEST['po_number'];
$po->reference_number = $_REQUEST['reference_number'];
// Vendor can't be changed after items have been received.
if (empty($po->received)) {
	$po->vendor = $config->run_sales->get_vendor(intval($_REQUEST['vendor']));
}
// Destination can't be changed after items have been received.
if (empty($po->received)) {
	$po->destination = $config->user_manager->get_group(intval($_REQUEST['destination']));
}
$po->shipper = $config->run_sales->get_shipper(intval($_REQUEST['shipper']));
$po->eta = strtotime($_REQUEST['eta']);

// Products
// Products can't be changed after items have been received.
if (empty($po->received)) {
	$po->products = json_decode($_REQUEST['products']);
	if (!is_array($po->products))
		$po->products = array();
	foreach ($po->products as &$cur_product) {
		$new_product = (object) array(
			"guid" => intval($cur_product->key),
			"quantity" => intval($cur_product->values[2]),
			"cost" => floatval($cur_product->values[3])
		);
		$cur_product = $new_product;
	}
	unset($cur_product);
}

if (empty($po->po_number)) {
	$module = $config->run_sales->print_po_form('com_sales', 'savepo');
	$module->entity = $po;
	display_error('Please specify a PO number.');
	return;
}
$test = $config->entity_manager->get_entities_by_data(array('po_number' => $po->po_number), array('com_sales', 'po'));
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
	$module = $config->run_sales->print_po_form('com_sales', 'savepo');
	$module->entity = $po;
	display_error('There is already a PO with that number. Please enter a different number.');
	return;
}
if (is_null($po->vendor)) {
	$module = $config->run_sales->print_po_form('com_sales', 'savepo');
	$module->entity = $po;
	display_error('Specified vendor is not valid.');
	return;
}
if (is_null($po->shipper)) {
	$module = $config->run_sales->print_po_form('com_sales', 'savepo');
	$module->entity = $po;
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