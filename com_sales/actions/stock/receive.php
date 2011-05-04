<?php
/**
 * Receive inventory.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/receive') )
	punt_user(null, pines_url('com_sales', 'stock/receive'));

if (!isset($_REQUEST['products'])) {
	$pines->com_sales->print_receive_form();
	return;
}

$products_json = (array) json_decode($_REQUEST['products']);
if (!$products_json) {
	pines_notice('Invalid product list!');
	$pines->com_sales->print_receive_form();
	return;
}
$products = array();
foreach ($products_json as $key => $cur_product) {
	$products[$key] = array(
		'product_code' => trim($cur_product->values[0]),
		'serial' => trim($cur_product->values[1]),
		'quantity' => (int) $cur_product->values[2]
	);
}

pines_session();
if (gatekeeper('com_sales/receivelocation') && isset($_REQUEST['location'])) {
	$location = group::factory((int) $_REQUEST['location']);
	if (!isset($location->guid)) {
		pines_notice('Specified location not found.');
		return;
	}
	if (!$location->is($_SESSION['user']->group) && !$location->in_array($_SESSION['user']->group->get_descendents())) {
		pines_notice('Specified location is not under yours.');
		return;
	}
} else {
	$location = $_SESSION['user']->group;
}

$module = new module('com_sales', 'stock/showreceived', 'content');
$module->location = $location;

foreach ($products as $cur_product) {
	$cur_product_entity = $pines->com_sales->get_product_by_code($cur_product['product_code']);
	if (!isset($cur_product_entity)) {
		pines_notice("Product with code {$cur_product['product_code']} not found! Skipping...");
		continue;
	}
	if ($cur_product_entity->serialized && empty($cur_product['serial'])) {
		pines_notice("Product [{$cur_product_entity->name}] with code {$cur_product['product_code']} requires a serial! Skipping...");
		continue;
	}
	for ($i = 0; $i < $cur_product['quantity']; $i++) {
		$origin = $stock = null;
		$serial = empty($cur_product['serial']) ? null : $cur_product['serial'];
		// Search for the product on a transfer.
		$origin = $pines->com_sales->get_origin_transfer($cur_product_entity, $serial, $location);
		if (isset($origin) && isset($origin[0])) {
			$stock = $origin[1];
			$origin = $origin[0];
			$status = 'received_transfer';
		} else {
			// Search for the product on a PO.
			$origin = $pines->com_sales->get_origin_po($cur_product_entity, $location);
			$stock = com_sales_stock::factory();
			$stock->product = $cur_product_entity;
			if ($cur_product_entity->serialized)
				$stock->serial = $serial;
			$stock->vendor = $origin->vendor;
			$status = 'received_po';
		}
		if (!isset($origin)) {
			pines_notice("Product [{$cur_product_entity->name}] with code {$cur_product['product_code']} was not found on any PO or transfer! Skipping...");
			continue;
		}
		
		$stock->receive($status, $origin, $location);
		$stock->save();

		$module->success[] = array($stock, $origin);
	}
}

?>