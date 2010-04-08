<?php
/**
 * com_sales_sheet class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A countsheet.
 *
 * @package com_sales
 */
class com_sales_countsheet extends entity {
	/**
	 * Load a countsheet.
	 * @param int $id The ID of the countsheet to load, 0 for a new countsheet.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'countsheet');
		// Defaults
		$this->status = 'pending';
		$this->entries = array();
		$this->products = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the countsheet.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted countsheet {$this->guid}.", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the countsheet.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_sales', 'form_countsheet', 'content');
		$module->entity = $this;
		
		return $module;
	}

	/**
	 * Print a form to review the countsheet.
	 * @return module The form's module.
	 */
	public function print_review() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_sales', 'form_countsheet_review', 'content');
		$module->entity = $this;

		$sold_status['available'] = 'in stock';
		$sold_status['unavailable'] = 'not for sale';
		$sold_status['sold_pending'] = 'sold (pending)';
		$sold_status['sold_at_store'] = 'sold on location';

		$in_stock = array('available', 'unavailable', 'sold_pending');
		$module->missing = $module->matched = $module->sold = $module->extraneous = $sold_array = $marked_items = array();
		// Grab all stock items for this location's inventory.
		$expected = $pines->entity_manager->get_entities(array('data' => array('gid' => $this->gid), 'tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
		foreach ($expected as $key => $checklist) {
			foreach ($this->entries as $itemkey => $item) {
				if (!isset($sold_array[$item->values[0]])) {
					$sold_array[$item->values[0]] = array();
					$sold_array[$item->values[0]]['found'] = false;
					//This will be unset later if there are no previously sold inventory items matching it.
					$sold_array[$item->values[0]]['entries'][] = 'Items matching "<strong>'.$item->values[0].'</strong>":<hr/>';
				}
				if ($checklist->serial == $item->values[0]) {
					if (in_array($checklist->status, $in_stock)) {
						// The serialized item is on the checklist & countsheet, its a MATCH.
						$module->matched[] = '#'.$item->values[0].' ('.$checklist->product->name.' SKU:['.$checklist->product->sku.'])';
						unset($expected[$key]);
						unset($this->entries[$itemkey]);
						break;
					} else {
						// A serialized item is not on the checklist but has a serial matching the search string
						if (!in_array($checklist->guid, $marked_items)) {
							$marked_items[] = $checklist->guid;
							//by ref = $checklist->product wont work either because of sales having products array instead of a product reference
							$sale = $pines->entity_manager->get_entity(array('ref' => array('products' => $checklist->product), 'tags' => array('com_sales', 'sale'), 'class' => com_sales_sale));
							$sold_array[$item->values[0]]['found'] = true;
							$sold_array[$item->values[0]]['entries'][] = '#'.$checklist->serial.' ('.$checklist->product->name.' SKU:['.$checklist->product->sku.']) was '.$sold_status[$checklist->status].'   -   '.$sale->guid;
						}
					}
				}
				if ($checklist->product->sku == $item->values[0] && !isset($checklist->serial)) {
					if (in_array($checklist->status, $in_stock)) {
						// The SKU item is on the checklist & countsheet, its a MATCH.
						$module->matched[] = $checklist->product->name.' SKU:['.$item->values[0].']';
						unset($expected[$key]);
						unset($this->entries[$itemkey]);
						break;
					} else {
						if (!in_array($checklist->guid, $marked_items)) {
							$marked_items[] = $checklist->guid;
							$sold_array[$item->values[0]]['found'] = true;
							$sold_array[$item->values[0]]['entries'][] = $checklist->product->name.' SKU:['.$checklist->product->sku.'] was '.$sold_status[$checklist->status];
						}
					}
				}
				if ($checklist->product->sku == $item->values[0] && isset($checklist->serial)) {
					// A serialized item is not on the checklist but has a SKU matching the search string.
					if (!in_array($checklist->guid, $marked_items)) {
						$marked_items[] = $checklist->guid;
						$sold_array[$item->values[0]]['found'] = true;
						$sold_array[$item->values[0]]['entries'][] = '#'.$checklist->serial.' ('.$checklist->product->name.' SKU:['.$checklist->product->sku.']) was '.$sold_status[$checklist->status];
					}
				}
			}
			if (isset($expected[$key]) && in_array($checklist->status, $in_stock)) {
				// An item from the checklist is missing on the countsheet.
				if (isset($checklist->serial)) {
					$module->missing[] = '#'.$checklist->serial.' ('.$checklist->product->name.' SKU:['.$checklist->product->sku.']) is '.$sold_status[$checklist->status];
				} else {
					$module->missing[] = $checklist->product->name.' SKU:['.$checklist->product->sku.'] is '.$sold_status[$checklist->status];
				}
			}
		}
		// See if any of the extraneous items matched any sold items in the inventory.
		foreach ($this->entries as $item) {
			if ($sold_array[$item->values[0]]['found'] == false) {
				// There were no potential matches for this unidentifed search string.
				$module->extraneous[] = '"'.$item->values[0].'" has no record in this location\'s inventory.';
				unset($sold_array[$item->values[0]]);
			} else {
				// Build the list of potential matches for this search string.
				foreach ($sold_array[$item->values[0]]['entries'] as $cur_sold) {
					$module->sold[] = $cur_sold;
				}
			}
		}
		unset($expected);
		unset($sold_array);
		unset($marked_items);
		return $module;
	}
}

?>