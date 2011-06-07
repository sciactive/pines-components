<?php
/**
 * Change a product on a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/changeproduct') )
	punt_user(null, pines_url('com_sales', 'forms/changeproduct'));

$sale = com_sales_sale::factory((int) $_REQUEST['id']);
if (!isset($sale->guid)) {
	pines_error('Requested sale id is not accessible.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}

$key = (int) $_REQUEST['product'];
$new_product = $pines->entity_manager->get_entity(
		array('class' => com_sales_product),
		array('&',
			'tag' => array('com_sales', 'product'),
			'strict' => array('sku', $_REQUEST['new_product'])
		)
	);

if (!isset($sale->products[$key])) {
	pines_notice('Invalid product key specified.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}
if (!isset($new_product->guid)) {
	pines_notice('Invalid product specified.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}
if ($sale->products[$key]['delivery'] != 'warehouse') {
	pines_notice('Product specified is not a warehouse item.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}
foreach ($sale->products[$key]['stock_entities'] as $cur_stock) {
	if (!$cur_stock->in_array((array) $sale->products[$key]['returned_stock_entities'])) {
		pines_notice('Product specified has already been fulfilled or partially fulfilled.');
		pines_redirect(pines_url('com_sales', 'sale/list'));
		return;
	}
}

pines_log("Changing product {$sale->products[$key]['entity']->name} [{$sale->products[$key]['entity']->sku}] on sale {$sale->id} to {$new_product->name} [{$new_product->sku}]", 'notice');
$sale->products[$key]['entity'] = $new_product;
$sale->products[$key]['sku'] = $new_product->sku;

if ($sale->save()) {
	pines_notice('Changed product on sale ['.$sale->id.']');
} else {
	pines_error('Error saving sale. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'sale/list'));

?>