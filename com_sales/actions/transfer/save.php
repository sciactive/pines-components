<?php
/**
 * Save changes to a transfer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') )
	punt_user(null, pines_url('com_sales', 'transfer/list'));

if ( isset($_REQUEST['id']) ) {
	$transfer = com_sales_transfer::factory((int) $_REQUEST['id']);
	if (!isset($transfer->guid)) {
		pines_error('Requested transfer id is not accessible.');
		return;
	}
} else {
	$transfer = com_sales_transfer::factory();
}

// General
// Certain items can't be changed after it's shipped.
if (!$transfer->final) {
	$transfer->reference_number = $_REQUEST['reference_number'];
	$transfer->origin = group::factory((int) $_REQUEST['origin']);
	if (!isset($transfer->origin->guid))
		$transfer->origin = null;
	$transfer->destination = group::factory((int) $_REQUEST['destination']);
	if (!isset($transfer->destination->guid))
		$transfer->destination = null;
	$transfer->products = (array) json_decode($_REQUEST['products']);
	foreach ($transfer->products as $key => &$cur_product) {
		$cur_product = com_sales_product::factory((int) $cur_product->values[0]);
		if (!isset($cur_product->guid))
			unset($transfer->product[$key]);
	}
	unset($cur_product);
}
$transfer->eta = strtotime($_REQUEST['eta']);

if (!$transfer->shipped) {
	$transfer->shipper = com_sales_shipper::factory((int) $_REQUEST['shipper']);
	if (!isset($transfer->shipper->guid))
		$transfer->shipper = null;
	// Can't change serials.
}

if (!isset($transfer->origin)) {
	$transfer->print_form();
	pines_error('Specified origin is not valid.');
	return;
}
if (!isset($transfer->destination)) {
	$transfer->print_form();
	pines_error('Specified destination is not valid.');
	return;
}

$transfer->ac->other = 2;

if ($_REQUEST['save'] == 'commit')
	$transfer->final = true;

if ($transfer->save()) {
	if ($transfer->final) {
		pines_notice('Committed transfer ['.$transfer->guid.']');
	} else {
		pines_notice('Saved transfer ['.$transfer->guid.']');
	}
} else {
	pines_error('Error saving transfer. Do you have permission?');
}

redirect(pines_url('com_sales', 'transfer/list'));

?>