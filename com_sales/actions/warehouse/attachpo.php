<?php
/**
 * Print a form to attach a PO to warehouse items.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/warehouse') )
	punt_user(null, pines_url('com_sales', 'warehouse/pending'));

$products = $product_entities = array();
foreach (explode(',', $_REQUEST['id']) as $cur_id) {
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

	// Save the item for PO search.
	$products[] = array('pending_products', $sale->products[(int) $key]['entity']);
	if (!$sale->products[(int) $key]['entity']->in_array($product_entities))
		$product_entities[] = $sale->products[(int) $key]['entity'];
}

if (!$products) {
	pines_notice('No products were selected.');
	pines_redirect(pines_url('com_sales', 'warehouse/pending'));
	return;
}

$pos = $pines->entity_manager->get_entities(
		array('class' => com_sales_po),
		array('&',
			'tag' => array('com_sales', 'po'),
			'data' => array('final', true),
			'ref' => $products
		)
	);

if (!$pos) {
	pines_notice('Couldn\'t find a PO with all the selected items.');
	pines_redirect(pines_url('com_sales', 'warehouse/pending'));
	return;
}

$module = new module('com_sales', 'warehouse/attach_po', 'content');
$module->pos = $pos;
$module->id = $_REQUEST['id'];
$module->products = $product_entities;

?>