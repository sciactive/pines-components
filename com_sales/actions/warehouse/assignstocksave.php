<?php
/**
 * Print a form to assign stock to warehouse items.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/warehouse') )
	punt_user(null, pines_url('com_sales', 'warehouse/pending'));

$items = (array) json_decode($_REQUEST['items'], true);

foreach ($items as $cur_item) {
	$cur_sale = com_sales_sale::factory((int) $cur_item['sale']);
	$stock = com_sales_stock::factory((int) $cur_item['guid']);
	$key = (int) $cur_item['key'];
	if (!isset($cur_sale->guid) || !isset($stock->guid) || !$stock->available) {
		pines_notice('Bad information provided, skipping this item...');
		continue;
	}
	// Check that the product matches the sale.
	if (!isset($cur_sale->products[$key]) || !$stock->product->is($cur_sale->products[$key]['entity'])) {
		pines_notice('Product doesn\'t match sale, skipping this item...');
		continue;
	}
	if ($cur_sale->products[$key]['delivery'] != 'warehouse') {
		pines_notice('Not a warehouse order, skipping this item...');
		continue;
	}
	// Check that the product still requires more entities.
	if (count($cur_sale->products[$key]['stock_entities']) - count($cur_sale->products[$key]['returned_stock_entities']) >= $cur_sale->products[$key]['quantity']) {
		pines_notice('Stock is already assigned, skipping this item...');
		continue;
	}
	// Remove the stock.
	if (!($stock->remove('sold_pending_shipping', $cur_sale, $stock->location) && $stock->save())) {
		pines_notice('Stock could not be removed, skipping this item...');
		continue;
	}
	pines_log("Setting stock entry $stock->guid to unavailable. It is being assigned to a warehouse order on sale $cur_sale->id.", 'info');
	$cur_sale->products[$key]['stock_entities'][] = $stock;
	if ($stock->product->serialized)
		$cur_sale->products[$key]['serial'] = $stock->serial;
	if (!$cur_sale->save()) {
		pines_error('Sale could not be saved, returning stock to inventory and skipping this item...');
		$stock->receive('other', $cur_sale, $stock->location, false);
		pines_log("Setting stock entry $stock->guid to available. It could not be assigned to a warehouse order on sale $cur_sale->id.", 'warning');
		$stock->save();
		continue;
	}
	$success = true;
}

if ($success)
	pines_notice('Assigned stock to selected orders.');

pines_redirect(pines_url('com_sales', 'warehouse/assigned'));

?>