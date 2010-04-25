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
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'receive'));

if (!isset($_REQUEST['products'])) {
	$pines->com_sales->print_receive_form();
	return;
}

$products_json = (array) json_decode($_REQUEST['products']);
if (empty($products_json)) {
	pines_notice('Invalid product list!');
	$pines->com_sales->print_receive_form();
	return;
}
$products = array();
foreach ($products_json as $key => $cur_product) {
	$products[$key] = array(
		'product_code' => $cur_product->values[0],
		'serial' => $cur_product->values[1],
		'quantity' => intval($cur_product->values[2])
	);
}

$module = new module('com_sales', 'show_received', 'content');

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
		$stock_entity = com_sales_stock::factory();
		$stock_entity->product = $cur_product_entity;
		if ($cur_product_entity->serialized)
			$stock_entity->serial = $cur_product['serial'];
		$origin = $stock_entity->inventory_origin();
		if (!isset($origin)) {
			pines_notice("Product [{$cur_product_entity->name}] with code {$cur_product['product_code']} was not found on any PO or transfer! Skipping...");
			continue;
		}
		// Replace the stock entry with the one returned by inventory_origin.
		$stock_entity = $origin[1];
		
		$stock_entity->receive($origin[0]);
		$stock_entity->ac = (object) array('user' => 2, 'group' => 2, 'other' => 2);
		$stock_entity->save();

		$module->success[] = array($stock_entity, $origin[0]);
	}
}

?>