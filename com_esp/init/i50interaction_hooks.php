<?php
/**
 * Create and register Extended Service Plans.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Update all related ESPs for sales when they are saved.
 *
 * @param array &$arguments Unused.
 * @param mixed $name Unused.
 * @param object &$object The sale being saved.
 */
function com_esp__check_sale(&$arguments, $name, &$object) {
	global $pines;

	if ($object->status == 'quoted')
		return;

	foreach ($object->products as $cur_product) {
		$cur_esp = $cur_product['esp'];
		if (empty($cur_esp))
			continue;
		// Find the stock entity for the current product.
		foreach ((array) $cur_product['stock_entities'] as $cur_stock) {
			if (!$cur_stock->in_array($cur_product['returned_stock_entities'])) {
				$stock = $cur_stock;
				break;
			}
		}
		if (!isset($stock->guid)) {
			pines_log('Unable to locate stock entity for ['.$cur_product['sku'].'] during ESP registration ['.$cur_esp.'].', 'info');
			continue;
		}
		// Find an existing ESP for the current item.
		$exisiting_esp = $pines->entity_manager->get_entity(
				array('class' => com_esp_plan),
				array('&',
					'tag' => array('com_esp', 'esp'),
					'strict' => array('unique_id', $cur_esp)
				)
			);
		if ($exisiting_esp->status == 'voided')
			continue;
		if (isset($exisiting_esp->guid)) {
			// Void an ESP if the sale has been returned or voided.
			if ($object->status == 'voided' || $cur_product['returned_quantity'] >= $cur_product['quantity']) {
				$exisiting_esp->status = 'voided';
				if ($exisiting_esp->save()) {
					pines_log('Canceled an ESP for ['.$exisiting_esp->customer->name.'].', 'notice');
				} else {
					pines_error('Error saving the ESP. Do you have permission?');
				}
			} else {
				// Skip it if it's already registered.
				if ($exisiting_esp->status == 'registered')
					continue;
				// Add the card/item and register the ESP.
				if (!isset($exisiting_esp->card->guid) && $cur_product['entity']->guid == $pines->config->com_esp->esp_product) {
					$exisiting_esp->card = $stock;
				} elseif (!isset($exisiting_esp->item->guid) && $cur_product['entity']->guid != $pines->config->com_esp->esp_product) {
					$exisiting_esp->item = $stock;
				}
				$exisiting_esp->status = 'registered';
				if ($exisiting_esp->save()) {
					pines_log('Registered an ESP for ['.$exisiting_esp->customer->name.']', 'notice');
				} else {
					pines_error('Error saving the ESP. Do you have permission?');
				}
			}
		} else {
			// Create a new ESP.
			$new_esp = com_esp_plan::factory();
			$new_esp->sale = $object;
			$new_esp->customer = $object->customer;
			$new_esp->unique_id = $cur_esp;
			$new_esp->expiration_date = strtotime('+'.$pines->config->com_esp->esp_term.' years', time());
			// Add the card or item to the ESP
			if ($cur_product['entity']->guid == $pines->config->com_esp->esp_product)
				$new_esp->card = $stock;
			else
				$new_esp->item = $stock;
			$new_esp->status = 'pending';
			if ($new_esp->save()) {
				pines_log('Created ESP '.$esp->id.' for ['.$esp->customer->name.'].');
			} else {
				pines_error('Error saving the ESP. Do you have permission?');
			}
		}
	}
}

$pines->hook->add_callback('com_sales_sale->save', 10, 'com_esp__check_sale');

?>