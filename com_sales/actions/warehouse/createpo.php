<?php
/**
 * Create a PO out of warehouse orders.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/warehouse') || !gatekeeper('com_sales/newpo') )
	punt_user(null, pines_url('com_sales', 'warehouse/pending'));

$location = null;
$vendors = null;
$products = array();
$item_ids = array();

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

	// Validate all the selected items.
	if (isset($sale->products[(int) $key]['po']->guid)) {
		pines_notice('All selected orders must not have attached POs.');
		throw new HttpServerException(null, 400);
	}

	if (!isset($location))
		$location = $sale->group;
	elseif (!$location->is($sale->group)) {
		pines_notice('All selected orders must have the same location.');
		throw new HttpServerException(null, 400);
	}
	$product = $sale->products[(int) $key]['entity'];
	if (!isset($vendors)) {
		$vendors = array();
		foreach ($product->vendors as $cur_vendor)
			if (isset($cur_vendor['entity']->guid))
				$vendors[] = $cur_vendor['entity'];
	} else {
		$cur_vendors = array();
		foreach ($product->vendors as $cur_vendor)
			$cur_vendors[] = $cur_vendor['entity'];
		foreach ($vendors as $vkey => $cur_vendor)
			if (!$cur_vendor->in_array($cur_vendors))
				unset($vendors[$vkey]);
		if (!$vendors) {
			pines_notice('All selected orders must have at least one vendor in common.');
			throw new HttpServerException(null, 400);
		}
	}

	// Keep track of all the products.
	if ($products[$product->guid])
		$products[$product->guid]['quantity'] += $sale->products[(int) $key]['quantity'];
	else
		$products[$product->guid] = array('entity' => $product, 'quantity' => $sale->products[(int) $key]['quantity'], 'cost' => 0);

	// Save the valid item IDs.
	$item_ids[] = "{$sale_id}_{$key}";
}
$item_ids = implode(',', $item_ids);

foreach ($products as &$cur_product) {
	foreach ($cur_product['entity']->vendors as $cur_vendor) {
		if ($vendors[0]->is($cur_vendor['entity'])) {
			$cur_product['cost'] = $cur_vendor['cost'];
			break;
		}
	}
}
unset($cur_product);

// Make a new PO with the details already filled.
$po = com_sales_po::factory();
$po->destination = $location;
$po->vendor = $vendors[0];
$po->products = array_values($products);
// Print the PO form and set the defaults.
$module = $po->print_form();
$module->locations = array($location);
$module->vendors = $vendors;
$module->location_fixed = true;
$module->item_ids = $item_ids;

?>