<?php
/**
 * Print a form to assign stock to warehouse items.
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

$items = array();
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

	// Save the item in an organized array.
	if (!isset($items[$sale->guid]))
		$items[$sale->guid] = array('sale' => $sale, 'products' => array());
	$items[$sale->guid]['products'][(int) $key] = $sale->products[(int) $key];
}

if (!$items) {
	pines_notice('No items were selected.');
	pines_redirect(pines_url('com_sales', 'warehouse/pending'));
	return;
}

$module = new module('com_sales', 'warehouse/assign_stock', 'content');
$module->items = $items;

?>