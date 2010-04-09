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
	 *  <li>Sold - List of items that match the search string, which the user may have included by accident:
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
		$pines->com_pgrid->load();
		$module = new module('com_sales', 'form_countsheet_review', 'content');
		$module->entity = $this;

		$sold_status['available'] = 'in stock';
		$sold_status['unavailable'] = 'not for sale';
		$sold_status['sold_pending'] = 'sold (pending)';
		$sold_status['sold_at_store'] = 'sold';

		$in_stock = array('available', 'unavailable', 'sold_pending');
		$module->missing = $module->matched = $module->sold = $module->extra = array();
		// Grab all stock items for this location's inventory.
		$expected_stock = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
		foreach ($expected_stock as $key => $checklist) {
			$in_store = ($checklist->location->guid == $this->gid) ? true : false;
			foreach ($this->entries as $itemkey => $item) {
				if (!isset($module->sold[$item])) {
					$module->sold[$item] = array();
					$module->sold[$item]['name'] = $item;
					$module->sold[$item]['found'] = false;
					$module->sold[$item]['closest'] = array();
					$module->sold[$item]['entries'] = array();
				}
				if ($checklist->serial == $item ||
					($checklist->product->sku == $item && !isset($checklist->serial))) {
					if (in_array($checklist->status, $in_stock)) {
						// The serialized item is on the checklist & countsheet, its a MATCH.
						$module->matched[] = $checklist;
						unset($expected_stock[$key]);
						unset($this->entries[$itemkey]);
						unset($module->sold[$item]);
						break;
					} else {
						// A serialized item is not on the checklist but has a serial matching the search string
						if (!in_array($checklist, $module->sold[$item]['entries'])) {
							$module->sold[$item]['found'] = true;
							$module->sold[$item]['entries'][] = $checklist;
						}
					}
				} else if ($checklist->product->sku == $item && isset($checklist->serial)) {
					// A serialized item is not on the checklist but has a SKU matching the search string.
					if (!in_array($checklist, $module->sold[$item]['closest']) && $in_store) {
						$module->sold[$item]['found'] = true;
						$module->sold[$item]['closest'][] = $checklist;
					} else if (!in_array($checklist, $module->sold[$item]['entries']) && !$in_store) {
						$module->sold[$item]['found'] = true;
						$module->sold[$item]['entries'][] = $checklist;
					} 
				}
			}
			if (isset($expected_stock[$key]) && in_array($checklist->status, $in_stock) && $in_store) {
				// An item from the checklist is missing on the countsheet.
				if (isset($checklist->serial)) {
					$module->missing[] = $checklist;
				} else {
					$module->missing[] = $checklist;
				}
			}
		}
		// See if any of the extraneous items matched any sold items in the inventory.
		foreach ($this->entries as $item) {
			if ($module->sold[$item]['found'] == false) {
				// There were no potential matches for this unidentifed search string.
				$module->extra[] = $item;
				unset($module->sold[$item]);
			}
		}
		return $module;
	}
}

?>