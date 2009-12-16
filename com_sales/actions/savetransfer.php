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
	$transfer = $config->run_sales->get_transfer($_REQUEST['id']);
	if (is_null($transfer)) {
		display_error('Requested transfer id is not accessible');
		return;
	}
} else {
	$transfer = new entity('com_sales', 'transfer');
}

// General
$transfer->reference_number = $_REQUEST['reference_number'];
// Destination can't be changed after items have been received.
if (empty($transfer->received)) {
	$transfer->destination = $config->user_manager->get_group(intval($_REQUEST['destination']));
}
$transfer->shipper = $config->run_sales->get_shipper(intval($_REQUEST['shipper']));
$transfer->eta = strtotime($_REQUEST['eta']);

// Stock
// Stock can't be changed after items have been received.
if (empty($transfer->received)) {
	$transfer->stock = json_decode($_REQUEST['stock']);
	if (!is_array($transfer->stock))
		$transfer->stock = array();
	foreach ($transfer->stock as $key => &$cur_stock) {
		$cur_stock = intval($cur_stock->key);
		if (is_null($config->entity_manager->get_entity($cur_stock, array('com_sales', 'stock'), com_sales_stock)))
			unset($transfer->stock[$key]);
	}
	unset($cur_stock);
}

if (is_null($transfer->destination)) {
	$module = $config->run_sales->print_transfer_form('com_sales', 'savetransfer');
	$module->entity = $transfer;
	display_error('Specified destination is not valid.');
	return;
}
if (is_null($transfer->shipper)) {
	$module = $config->run_sales->print_transfer_form('com_sales', 'savetransfer');
	$module->entity = $transfer;
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