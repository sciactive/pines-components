<?php
/**
 * Save shipping info to a transfer.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/shipstock') )
	punt_user(null, pines_url('com_sales', 'transfer/list'));

$transfer = com_sales_transfer::factory((int) $_REQUEST['id']);
if (!isset($transfer->guid)) {
	pines_error('Requested transfer id is not accessible.');
	return;
}
if (!$transfer->final) {
	pines_notice('Requested transfer id is not committed.');
	return;
}
if ($transfer->shipped) {
	pines_notice('Requested transfer id is already shipped.');
	return;
}

$serials = array();
$guids = array();
foreach ($transfer->products as $cur_product) {
	// Look up the stock entry using the given serials.
	$selector = array('&',
			'tag' => array('com_sales', 'stock'),
			'ref' => array(
				array('location', $transfer->origin->guid),
				array('product', $cur_product->guid)
			),
		);
	if ($cur_product->serialized) {
		if (!isset($serials[$cur_product->guid]))
			$serials[$cur_product->guid] = array_map('trim', explode("\n", trim($_REQUEST['serials_'.$cur_product->guid])));
		$selector['data'] = array('serial', array_pop($serials[$cur_product->guid]));
	}
	$stock = null;
	$stock_array = $pines->entity_manager->get_entities(
			array('class' => com_sales_stock, 'skip_ac' => true),
			$selector,
			array('!&',
				'guid' => $guids
			)
		);

	// Select the first available stock, else select unavailable stock.
	foreach ($stock_array as $cur_stock) {
		if ($cur_stock->available) {
			$stock = $cur_stock;
			break;
		}
	}
	if (!isset($stock))
		$stock = $stock_array[0];

	// Did we find the stock?
	if (isset($stock->guid)) {
		$guids[] = $stock->guid;
		$transfer->stock[] = $stock;
	} else {
		pines_notice('The product ['.$cur_product->name.']'.($cur_product->serialized ? " with the serial [{$selector['data'][1]}]" : '').' is not available in the inventory of '.$transfer->origin->name);
		$transfer->print_ship();
		return;
	}
}

if (!$transfer->save() || !$transfer->ship())
	pines_error('An error occurred while removing inventory. Make sure all inventory was removed properly.');

if ($transfer->save()) {
	pines_notice('Shipped transfer ['.$transfer->guid.']');
} else {
	pines_error('Error saving transfer. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'transfer/list'));

?>