<?php
/**
 * Save changes to a PO.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editpo') )
		punt_user(null, pines_url('com_sales', 'po/list'));
	$po = com_sales_po::factory((int) $_REQUEST['id']);
	if (!isset($po->guid)) {
		pines_error('Requested PO id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newpo') )
		punt_user(null, pines_url('com_sales', 'po/list'));
	$po = com_sales_po::factory();
}

// General
if (!$po->final)
	$po->po_number = $_REQUEST['po_number'];
$po->reference_number = $_REQUEST['reference_number'];
// Vendor can't be changed after items have been received.
if (!$po->final && empty($po->received)) {
	$po->vendor = com_sales_vendor::factory((int) $_REQUEST['vendor']);
	if (!isset($po->vendor->guid))
		$po->vendor = null;
}
// Destination can't be changed after items have been received.
if (!$po->final && empty($po->received)) {
	$po->destination = group::factory((int) $_REQUEST['destination']);
	if (!isset($po->destination->guid))
		$po->destination = null;
}
if (!$po->final) {
	$po->shipper = com_sales_shipper::factory((int) $_REQUEST['shipper']);
	if (!isset($po->shipper->guid))
		$po->shipper = null;
}
$po->eta = strtotime($_REQUEST['eta']);
$po->tracking_numbers = array_diff(array_map('trim', (array) explode("\n", trim($_REQUEST['tracking_numbers']))), array(''));

// Products
// Products can't be changed after items have been received.
if (!$po->final && empty($po->received)) {
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

if (!$po->final) {
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
}

if (!$po->final && $_REQUEST['save'] == 'commit') {
	$po->final = true;
	$send_email = true;
}
// Only comments can be cahnged after it is commited.
$po->comments = $_REQUEST['comments'];

// This happens when a PO is being created from warehouse orders.
if ($_REQUEST['item_ids'] && $po->final) {
	// Verify all the PO information, or kick them back to the edit page.
	$location = $po->destination;
	$vendors = array($po->vendor);
	$items = array();

	foreach (explode(',', $_REQUEST['item_ids']) as $cur_id) {
		list ($sale_id, $key) = explode('_', $cur_id);
		$sale = com_sales_sale::factory((int) $sale_id);
		if (!isset($sale->guid)) {
			pines_notice('Couldn\'t find specified sale.');
			continue;
		}

		if (!isset($sale->products[(int) $key])) {
			pines_notice('Couldn\'t find specified item.');
			continue;
		}

		if ($sale->products[(int) $key]['delivery'] != 'warehouse') {
			pines_notice('Specified item is not a warehouse order.');
			continue;
		}

		if (isset($sale->products[(int) $key]['po']->guid)) {
			pines_notice('All selected orders must not have attached POs.');
			$po->print_form();
			return;
		}

		if (!$location->is($sale->group)) {
			pines_notice('All selected orders must have the same location.');
			$po->print_form();
			return;
		}
		$product = $sale->products[(int) $key]['entity'];
		$cur_vendors = array();
		foreach ($product->vendors as $cur_vendor)
			$cur_vendors[] = $cur_vendor['entity'];
		foreach ($vendors as $vkey => $cur_vendor)
			if (!$cur_vendor->in_array($cur_vendors))
				unset($vendors[$vkey]);
		if (!$vendors) {
			pines_notice('All selected orders must have at least one vendor in common.');
			$po->print_form();
			return;
		}
		$items[] = array('sale' => $sale, 'key' => (int) $key);
	}
}

if ($po->save()) {
	if ($_REQUEST['save'] == 'commit')
		pines_notice('Committed PO ['.$po->po_number.']');
	else
		pines_notice('Saved PO ['.$po->po_number.']');
	if ($_REQUEST['item_ids'] && $po->final) {
		// Now attach the PO to its respective items for warehouse orders.
		foreach ($items as $cur_item) {
			$cur_item['sale']->products[$cur_item['key']]['po'] = $po;
			if (!$cur_item['sale']->save())
				pines_notice("Couldn't save sale #{$cur_item['sale']->id}. The item, {$cur_item['sale']->products[$cur_item['key']]['entity']->name}, couldn't be attached.");
		}
	}
	if ($send_email)
		$po->email();
} else {
	pines_error('Error saving PO. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'po/list'));

?>