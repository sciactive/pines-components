<?php
/**
 * Save changes to a transfer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listtransfers', null, false));
	return;
}

if ( isset($_REQUEST['id']) ) {
	$transfer = com_sales_transfer::factory((int) $_REQUEST['id']);
	if (is_null($transfer->guid)) {
		display_error('Requested transfer id is not accessible');
		return;
	}
} else {
	$transfer = com_sales_transfer::factory();
}

// General
$transfer->reference_number = $_REQUEST['reference_number'];
// Destination can't be changed after items have been received.
if (empty($transfer->received)) {
	$transfer->destination = group::factory(intval($_REQUEST['destination']));
	if (is_null($transfer->destination->guid))
		$transfer->destination = null;
}
$transfer->shipper = com_sales_shipper::factory(intval($_REQUEST['shipper']));
if (is_null($transfer->shipper->guid))
	$transfer->shipper = null;
$transfer->eta = strtotime($_REQUEST['eta']);

// Stock
// Stock can't be changed after items have been received.
if (empty($transfer->received)) {
	$transfer->stock = json_decode($_REQUEST['stock']);
	if (!is_array($transfer->stock))
		$transfer->stock = array();
	foreach ($transfer->stock as $key => &$cur_stock) {
		$cur_stock = com_sales_stock::factory(intval($cur_stock->key));
		if (is_null($cur_stock->guid))
			unset($transfer->stock[$key]);
	}
	unset($cur_stock);
}

if (is_null($transfer->destination)) {
	$transfer->print_form();
	display_error('Specified destination is not valid.');
	return;
}
if (is_null($transfer->shipper)) {
	$transfer->print_form();
	display_error('Specified shipper is not valid.');
	return;
}

$transfer->ac = (object) array('other' => 2);
if ($transfer->save()) {
	display_notice('Saved transfer ['.$transfer->guid.']');
} else {
	display_error('Error saving transfer. Do you have permission?');
}

$config->run_sales->list_transfers();
?>