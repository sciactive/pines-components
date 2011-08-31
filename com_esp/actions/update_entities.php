<?php
/**
 * Update ESP entities.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
    punt_user('You don\'t have necessary permission.', pines_url());

$module = new module('system', 'null', 'content');
$module->title = 'ESP Entity Update';

$plans = $pines->entity_manager->get_entities(array('class' => com_esp_plan), array('&', 'tag' => array('com_esp', 'esp')));

foreach ($plans as $plan) {
	// Only update the item for ESPs with a stock entity.
	if ($plan->sale_location == 'online')
		$plan->item = $plan->items[0];
	foreach ($plan->sale->products as $cur_product) {
		if ($cur_product['serial'] == $plan->card->serial) {
			if (!empty($cur_product['stock_entities'][0]->guid)) {
				$stock_id = $cur_product['stock_entities'][0]->guid;
			} elseif (!empty($cur_product['shipped_entities'][0]->guid)) {
				$stock_id = $cur_prduct['shipped_entities'][0]->guid;
			} elseif (!empty($cur_product['returned_stock_entities'][0]->guid)) {
				$stock_id = $cur_product['returned_stock_entities'][0]->guid;
			}
			$stock = com_sales_stock::factory((int) $stock_id);
			$plan->card = $stock;
		}
	}
	$plan->unique_id = unique_id;
	unset($plan->items);
	$plan->save();
}

?>