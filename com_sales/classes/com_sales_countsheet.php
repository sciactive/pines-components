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
 * @package Pines
 * @subpackage com_sales
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
		$this->matched = array();
		$this->matched_count = array();
		$this->matched_serials = array();
		$this->missing = array();
		$this->missing_count = array();
		$this->missing_serials = array();
		$this->potential = array();
		$this->invalid = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data(), $entity->get_sdata());
		}
	}

	/**
	 * Create a new instance.
	 * @return com_sales_countsheet The new instance.
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
		$module = new module('com_sales', 'countsheet/form', 'content');
		$module->entity = $this;
		
		return $module;
	}

	/**
	 * Sort by the location, then serial.
	 *
	 * @param mixed $a The first entry.
	 * @param mixed $b The second entry.
	 * @return int The sort order.
	 * @access private
	 */
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
	 * @return module The form's module.
	 */
	public function print_review() {
		global $pines;

		$this->run_count();

		$module = new module('com_sales', 'countsheet/formreview', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Count inventory for the countsheet.
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
	 *  <li>Invalid - If an item is not in the inventory at all.</li>
	 * </ul>
	 */
	public function run_count() {
		global $pines;
		// Committed countsheets can't be run again.
		if ($this->final)
			return;
		// Set the run date.
		$this->run_count_date = time();
		$not_selector = array('!&',
			'guid' => array(),
			'data' => array('location', null)
		);
		$and_selector = array('&',
			'tag' => array('com_sales', 'stock')
		);
		// Reset arrays.
		$this->matched = array();
		$this->matched_count = array();
		$this->matched_serials = array();
		$this->missing = array();
		$this->missing_count = array();
		$this->missing_serials = array();
		$this->potential = array();
		$this->invalid = array();
		$entries = array();
		foreach ($this->entries as $cur_entry) {
			for ($i=0; $i<$cur_entry->qty; $i++)
				$entries[] = $cur_entry->code;
		}
		// Find entries based on location and serial.
		foreach ($entries as $key => $cur_code) {
			$stock = $pines->entity_manager->get_entity(
					array('class' => com_sales_stock),
					$and_selector,
					$not_selector,
					array('&',
						'ref' => array('location', $this->group),
						'data' => array('serial', $cur_code)
					)
				);
			if (isset($stock)) {
				// If the product isn't serialized, something's wrong, don't save it.
				if (!$stock->product->serialized)
					continue;
				$this->matched[] = $stock;
				$this->matched_count[$stock->product->guid]++;
				$this->matched_serials[$stock->product->guid][] = $stock->serial;
				$not_selector['guid'][] = $stock->guid;
				unset($entries[$key]);
			}
		}
		// Find entries based on location and SKU/barcode.
		foreach ($entries as $key => $cur_code) {
			$product = $pines->com_sales->get_product_by_code($cur_code);
			if (!isset($product))
				continue;
			$stock = $pines->entity_manager->get_entity(
					array('class' => com_sales_stock),
					$and_selector,
					$not_selector,
					array('&',
						'ref' => array(array('location', $this->group), array('product', $product))
					)
				);
			if (isset($stock)) {
				// If the product is serialized, the entry is incorrect.
				if ($stock->product->serialized) {
					if (!$stock->in_array($this->potential[$cur_code]['closest'])) {
						$this->potential[$cur_code]['name'] = $cur_code;
						// Closest, since it's in this location.
						$this->potential[$cur_code]['closest'][] = $stock;
					}
					$this->potential[$cur_code]['count']++;
				} else {
					$this->matched[] = $stock;
					$this->matched_count[$stock->product->guid]++;
					$this->matched_serials[$stock->product->guid] = array();
					$not_selector['guid'][] = $stock->guid;
				}
				unset($entries[$key]);
			}
		}
		// Find entries based on serial.
		foreach ($entries as $key => $cur_code) {
			$stocks = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_stock, 'limit' => 5),
					$and_selector,
					$not_selector,
					array('&',
						'data' => array('serial', $cur_code)
					),
					array('!&',
						'ref' => array('location', $this->group)
					)
				);
			if ($stocks) {
				foreach ($stocks as $stock) {
					// If the product isn't serialized, something's wrong, don't save it.
					if (!$stock->product->serialized)
						continue;
					if (!$stock->in_array($this->potential[$cur_code]['entries'])) {
						$this->potential[$cur_code]['name'] = $cur_code;
						// Entries, since it's in another location.
						$this->potential[$cur_code]['entries'][] = $stock;
					}
				}
				$this->potential[$cur_code]['count']++;
				unset($entries[$key]);
			}
		}
		// Find entries based on SKU/barcode.
		foreach ($entries as $key => $cur_code) {
			$product = $pines->com_sales->get_product_by_code($cur_code);
			if (!isset($product))
				continue;
			$stocks = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_stock, 'limit' => 5),
					$and_selector,
					$not_selector,
					array('&',
						'ref' => array('product', $product)
					),
					array('!&',
						'ref' => array('location', $this->group)
					)
				);
			if ($stocks) {
				foreach ($stocks as $stock) {
					if (!$stock->in_array($this->potential[$cur_code]['entries'])) {
						$this->potential[$cur_code]['name'] = $cur_code;
						// Entries, since it's in another location.
						$this->potential[$cur_code]['entries'][] = $stock;
					}
				}
				$this->potential[$cur_code]['count']++;
				unset($entries[$key]);
			}
		}
		// All the rest are invalid.
		$this->invalid = $entries;
		// Find entries that should be counted, but weren't found.
		$this->missing = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_stock),
				$and_selector,
				$not_selector,
				array('&',
					'ref' => array('location', $this->group)
				)
			);
		foreach ($this->missing as $stock) {
			$this->missing_count[$stock->product->guid]++;
			if ($stock->product->serialized) {
				$this->missing_serials[$stock->product->guid][] = $stock->serial;
			} else {
				$this->missing_serials[$stock->product->guid] = array();
			}
		}
	}
}

?>