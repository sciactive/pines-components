<?php
/**
 * Save changes to a PO.
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
	if ( !gatekeeper('com_sales/editpo') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listpos', null, false));
	$po = com_sales_po::factory((int) $_REQUEST['id']);
	if (is_null($po->guid) || $po->final) {
		pines_error('Requested PO id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newpo') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listpos', null, false));
	$po = com_sales_po::factory();
}

// General
$po->po_number = $_REQUEST['po_number'];
$po->reference_number = $_REQUEST['reference_number'];
// Vendor can't be changed after items have been received.
if (empty($po->received)) {
	$po->vendor = com_sales_vendor::factory(intval($_REQUEST['vendor']));
	if (is_null($po->vendor->guid))
		$po->vendor = null;
}
// Destination can't be changed after items have been received.
if (empty($po->received)) {
	$po->destination = group::factory(intval($_REQUEST['destination']));
	if (is_null($po->destination->guid))
		$po->destination = null;
}
$po->shipper = com_sales_shipper::factory(intval($_REQUEST['shipper']));
if (is_null($po->shipper->guid))
	$po->shipper = null;
$po->eta = strtotime($_REQUEST['eta']);

// Products
// Products can't be changed after items have been received.
if (empty($po->received)) {
	$po->products = (array) json_decode($_REQUEST['products']);
	foreach ($po->products as &$cur_product) {
		$cur_product = array(
			'entity' => com_sales_product::factory(intval($cur_product->key)),
			'quantity' => intval($cur_product->values[2]),
			'cost' => floatval($cur_product->values[3])
		);
		if (is_null($cur_product['entity']->guid))
			$cur_product['entity'] = null;
	}
	unset($cur_product);
}

if (empty($po->po_number)) {
	$po->print_form();
	pines_notice('Please specify a PO number.');
	return;
}
$test = $pines->entity_manager->get_entity(array('data' => array('po_number' => $po->po_number), 'tags' => array('com_sales', 'po'), 'class' => com_sales_po));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$po->print_form();
	pines_notice('There is already a PO with that number. Please enter a different number.');
	return;
}
if (is_null($po->vendor)) {
	$po->print_form();
	pines_error('Specified vendor is not valid.');
	return;
}
if (is_null($po->shipper)) {
	$po->print_form();
	pines_error('Specified shipper is not valid.');
	return;
}

$po->ac->other = 2;

if ($_REQUEST['save'] == 'commit')
	$po->final = true;

if ($po->save()) {
	if ($po->final) {
		pines_notice('Committed PO ['.$po->guid.']');
	} else {
		pines_notice('Saved PO ['.$po->guid.']');
	}
} else {
	pines_error('Error saving PO. Do you have permission?');
}

$pines->com_sales->list_pos();
?>