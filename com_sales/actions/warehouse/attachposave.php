<?php
/**
 * Attach a PO to warehouse items.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/warehouse') )
	punt_user(null, pines_url('com_sales', 'warehouse/pending'));

$products = $product_entities = array();
foreach (explode(',', $_REQUEST['id']) as $cur_id) {
	list ($sale_id, $key) = explode('_', $cur_id);
	$sale = com_sales_sale::factory((int) $sale_id);
	// They should have already been notified about these problems.
	if (!isset($sale->guid))
		continue;

	if (!isset($sale->products[(int) $key]))
		continue;

	if ($sale->products[(int) $key]['delivery'] != 'warehouse')
		continue;

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

// Now see if their selected PO is in there.
$po = com_sales_po::factory((int) $_REQUEST['po']);
if (!isset($po->guid)) {
	pines_notice('Specified PO could not be found.');
	pines_redirect(pines_url('com_sales', 'warehouse/pending'));
	return;
}

if (!$po->in_array($pos)) {
	pines_notice('Specified PO does not contain all the specified items.');
	pines_redirect(pines_url('com_sales', 'warehouse/pending'));
	return;
}

// Go through the items again, attaching the PO this time.
foreach (explode(',', $_REQUEST['id']) as $cur_id) {
	list ($sale_id, $key) = explode('_', $cur_id);
	$sale = com_sales_sale::factory((int) $sale_id);
	// They should have already been notified about these problems.
	if (!isset($sale->guid))
		continue;

	if (!isset($sale->products[(int) $key]))
		continue;

	if ($sale->products[(int) $key]['delivery'] != 'warehouse')
		continue;
	
	// Remember where to go.
	$ordered = $sale->products[(int) $key]['ordered'];

	$sale->products[(int) $key]['po'] = $po;
	if ($sale->save())
		$success = true;
	else
		pines_notice("Couldn't save sale #{$sale->id}.");
}

if ($success)
	pines_notice('Attached PO to selected items.');

pines_redirect(pines_url('com_sales', 'warehouse/pending', array('ordered' => ($ordered ? 'true' : 'false'))));

?>