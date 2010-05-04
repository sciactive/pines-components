<?php
/**
 * com_sales_countsheet class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
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
			if (!isset($entity))
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
		$module = new module('com_sales', 'form_countsheet', 'content');
		$module->entity = $this;
		
		return $module;
	}

	private function sort_stock_by_location_serial($a, $b) {
		if ($a->location->guid == $this->group->guid && $b->location->guid != $this->group->guid)
			return -1;
		if ($a->location->guid != $this->group->guid && $b->location->guid == $this->group->guid)
			return 1;
		if (isset($a->serial) && !isset($b->serial))
			return -1;
		if (!isset($a->serial) && isset($b->serial))
			return 1;
		return 0;
	}

	/**
	 * Print a form to review the countsheet.
	 *
	 * This function searches through all items in the inventory of the
	 * countsheet's location. It compares the countsheet entries to each item
	 * and checks off matching items. Leftovers create a missing items list,
	 * while unidentified entries either have potential matches or are
	 * extraneous.
	 *
	 * Items are broken down into these categories:
	 *
	 * <ul>
	 *  <li>Matched - If an item is on the countsheet and in the current inventory.</li>
	 *  <li>Missing - If an item is not on the countsheet but is in the current inventory.</li>
	 *  <li>Potential - List of items that match the search string, which the user may have included by accident:
	 *	 <ul>
	 *    <li>A serialized item matches the search string but is not in current inventory.</li>
	 *    <li>An item's SKU matches the search string but is not in the current inventory.</li>
	 *   </ul>
	 *  <li>Extra - If an item is not in the inventory at all.</li>
	 * </ul>
	 * 
	 * @return module The form's module.
	 */
	public function print_review() {
		global $pines;
		$module = new module('com_sales', 'form_countsheet_review', 'content');
		$module->entity = $this;

		$sold_status['available'] = 'in stock';
		$sold_status['unavailable'] = 'not for sale';
		$sold_status['sold_pending'] = 'sold (pending)';
		$sold_status['sold_at_store'] = 'sold';

		$in_stock = array('available', 'unavailable', 'sold_pending');
		$module->missing = $module->matched = $module->potential = $module->extra = array();
		// Grab all stock items for this location's inventory.
		$expected_stock = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
		usort($expected_stock, array($this, 'sort_stock_by_location_serial'));
		foreach ($expected_stock as $key => $cur_stock_entry) {
			$entry_exists = false;
			$in_store = ($cur_stock_entry->location->guid == $this->group->guid);
			foreach ($this->entries as $itemkey => $item) {
				if (!isset($module->potential[$item])) {
					$module->potential[$item] = array(
						'name' => $item,
						'found' => false,
						'closest' => array(),
						'entries' => array()
					);
				}
				if ($cur_stock_entry->serial == $item || ($cur_stock_entry->product->sku == $item && !isset($cur_stock_entry->serial))) {
					// The item is a serial match; or it is a sku match, and the entry is not serialized.
					if ($in_store) {
						// The item is found.
						foreach ($module->matched as $cur_matched) {
							if (!isset($cur_matched->serial) && $cur_stock_entry->product->sku == $cur_matched->product->sku) {
								$module->matched_count[$cur_stock_entry->product->sku]++;
								$entry_exists = true;
							}
						}
						if (!$entry_exists) {
							$module->matched[] = $cur_stock_entry;
							$module->matched_count[$cur_stock_entry->product->sku] = 1;
						}
						unset($expected_stock[$key]);
						unset($this->entries[$itemkey]);
						// Clear out the 'potential' entry for this item string.
						if (!$module->potential[$item]['found'])
							unset($module->potential[$item]);
						break;
					} elseif (!$cur_stock_entry->in_array($module->potential[$item]['entries'])) {
						// The item is not in the location but matches the search string.
						$module->potential[$item]['found'] = true;
						$module->potential[$item]['entries'][] = $cur_stock_entry;
					}
				} elseif ($cur_stock_entry->product->sku == $item && isset($cur_stock_entry->serial)) {
					// A serialized item has a SKU matching the search string.
					$module->potential[$item]['found'] = true;
					if ($in_store) {
						if (!$cur_stock_entry->in_array($module->potential[$item]['closest']))
							$module->potential[$item]['closest'][] = $cur_stock_entry;
					} else {
						if (!$cur_stock_entry->in_array($module->potential[$item]['entries']))
							$module->potential[$item]['entries'][] = $cur_stock_entry;
					}
				}
			}
			if ($in_store && isset($expected_stock[$key])) {
				// A stock entry at this location is missing on the countsheet.
				foreach ($module->missing as $cur_missing) {
					if (!isset($cur_missing->serial) && $cur_stock_entry->product->sku == $cur_missing->product->sku) {
						$module->missing_count[$cur_stock_entry->product->sku]++;
						$entry_exists = true;
					}
				}
				if (!$entry_exists) {
					$module->missing[] = $cur_stock_entry;
					$module->missing_count[$cur_stock_entry->product->sku] = 1;
				}
			}
		}
		// See if any of the extraneous items matched any sold items in the inventory.
		foreach ($this->entries as $item) {
			if ($module->potential[$item]['found'] == false) {
				// There were no potential matches for this search string.
				$module->extra[] = $item;
				unset($module->potential[$item]);
			}
		}
		return $module;
	}
}

?>