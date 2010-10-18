<?php
/**
 * Save changes to a PO.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editpo') )
		punt_user(null, pines_url('com_sales', 'po/list'));
	$po = com_sales_po::factory((int) $_REQUEST['id']);
	if (!isset($po->guid) || $po->final) {
		pines_error('Requested PO id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newpo') )
		punt_user(null, pines_url('com_sales', 'po/list'));
	$po = com_sales_po::factory();
}

// General
$po->po_number = $_REQUEST['po_number'];
$po->reference_number = $_REQUEST['reference_number'];
// Vendor can't be changed after items have been received.
if (empty($po->received)) {
	$po->vendor = com_sales_vendor::factory((int) $_REQUEST['vendor']);
	if (!isset($po->vendor->guid))
		$po->vendor = null;
}
// Destination can't be changed after items have been received.
if (empty($po->received)) {
	$po->destination = group::factory((int) $_REQUEST['destination']);
	if (!isset($po->destination->guid))
		$po->destination = null;
}
$po->shipper = com_sales_shipper::factory((int) $_REQUEST['shipper']);
if (!isset($po->shipper->guid))
	$po->shipper = null;
$po->eta = strtotime($_REQUEST['eta']);

// Products
// Products can't be changed after items have been received.
if (empty($po->received)) {
	$po->products = (array) json_decode($_REQUEST['products']);
	foreach ($po->products as &$cur_product) {
		$cur_product = array(
			'entity' => com_sales_product::factory((int) $cur_product->key),
			'quantity' => (int) $cur_product->values[2],
			'cost' => (float) $cur_product->values[3]
		);
		if (!isset($cur_product['entity']->guid))
			$cur_product['entity'] = null;
	}
	unset($cur_product);
}

if (empty($po->po_number)) {
	$po->po_number = 'PO';
	if (isset($po->destination))
		$po->po_number .= strtoupper($po->destination->name);
	$po->po_number .= '-'.$pines->entity_manager->new_uid('com_sales_po');
}
$test = $pines->entity_manager->get_entity(array('class' => com_sales_po, 'skip_ac' => true), array('&', 'tag' => array('com_sales', 'po'), 'data' => array('po_number', $po->po_number)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$po->print_form();
	pines_notice('There is already a PO with that number. Please enter a different number.');
	return;
}
if (!isset($po->vendor)) {
	$po->print_form();
	pines_error('Specified vendor is not valid.');
	return;
}
if (!isset($po->shipper)) {
	$po->print_form();
	pines_error('Specified shipper is not valid.');
	return;
}

$po->ac->other = 2;

if ($_REQUEST['save'] == 'commit')
	$po->final = true;

if ($po->save()) {
	if ($po->final) {
		pines_notice('Committed PO ['.$po->po_number.']');
	} else {
		pines_notice('Saved PO ['.$po->po_number.']');
	}
} else {
	pines_error('Error saving PO. Do you have permission?');
}

redirect(pines_url('com_sales', 'po/list'));

?>