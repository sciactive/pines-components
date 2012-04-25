<?php
/**
 * List warehouse items that need to be ordered.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/viewwarehouse') && !gatekeeper('com_sales/warehouse') )
	punt_user(null, pines_url('com_sales', 'warehouse/pending_info'));

$pines->page->override = true;

list ($sale_id, $key) = explode('_', $_REQUEST['id']);
$sale = com_sales_sale::factory((int) $sale_id);
if (!isset($sale->guid)) {
	$pines->page->override_doc('Couldn\'t find specified sale.');
	return;
}

$product = $sale->products[(int) $key]['entity'];
if (!isset($product->guid)) {
	$pines->page->override_doc('Couldn\'t find specified product.');
	return;
}

// Warehouse group.
$warehouse = group::factory($pines->config->com_sales->warehouse_group);
if (!isset($warehouse->guid)) {
	pines_error('Warehouse group is not configured correctly.');
	return;
}

$module = new module('com_sales', 'warehouse/pending_info');

// Find warehouse stock.
$module->warehouse = $pines->entity_manager->get_entities(
		array('class' => com_sales_stock, 'skip_ac' => true),
		array('&',
			'tag' => array('com_sales', 'stock'),
			'data' => array('available', true),
			'ref' => array(
				array('location', $warehouse),
				array('product', $product)
			)
		)
	);

// Find PO products.
$module->pos = (array) $pines->entity_manager->get_entities(
		array('class' => com_sales_po, 'skip_ac' => true),
		array('&',
			'tag' => array('com_sales', 'po'),
			'data' => array(array('final', true), array('finished', false)),
			'ref' => array(
				array('destination', $warehouse),
				array('pending_products', $product)
			)
		)
	);

// Find transfer products.
$module->transfers = (array) $pines->entity_manager->get_entities(
		array('class' => com_sales_transfer, 'skip_ac' => true),
		array('&',
			'tag' => array('com_sales', 'transfer'),
			'data' => array(array('final', true), array('shipped', true), array('finished', false)),
			'ref' => array(
				array('destination', $warehouse),
				array('pending_products', $product)
			)
		)
	);

// Find item in current inventory.
$stock = (array) $pines->entity_manager->get_entities(
		array('class' => com_sales_stock, 'skip_ac' => true),
		array('&',
			'tag' => array('com_sales', 'stock'),
			'data' => array('available', true),
			'isset' => 'location',
			'ref' => array('product', $product)
		),
		array('!&',
			'ref' => array('location', $warehouse)
		)
	);
$module->locations = array();
$module->locations_serials = array();
foreach ($stock as $cur_stock) {
	if (!isset($cur_stock->location->guid))
		continue;
	if (!$cur_stock->location->in_array($module->locations))
		$module->locations[] = $cur_stock->location;
	$module->locations_serials[$cur_stock->location->guid][] = $cur_stock->serial;
}

$module->product = $product;

$pines->page->override_doc($module->render());

?>