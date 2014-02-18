<?php
/**
 * com_sales_countsheet class.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A countsheet.
 *
 * @package Components\sales
 */
class com_sales_countsheet extends entity {
	/**
	 * Load a countsheet.
	 * @param int $id The ID of the countsheet to load, 0 for a new countsheet.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'countsheet');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults
		$this->status = 'pending';
		$this->entries = $this->search_strings = array();
		$this->matched = $this->missing = $this->potential = $this->invalid = array();
		$this->matched_count = $this->missing_count = array();
		$this->matched_serials = $this->missing_serials = array();
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

	public function info($type) {
		switch ($type) {
			case 'name':
				return "Countsheet $this->guid";
			case 'type':
				return 'countsheet';
			case 'types':
				return 'countsheets';
			case 'url_edit':
				if (gatekeeper('com_sales/editcountsheet'))
					return pines_url('com_sales', 'countsheet/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listcountsheets'))
					return pines_url('com_sales', 'countsheet/list');
				break;
			case 'icon':
				return 'picon-view-task';
		}
		return null;
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
         * 
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;

		if (!isset($this->group->guid))
			$this->group = $_SESSION['user']->group;

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
	 *  <li>Duplicate - If an item is a duplicate of a matched item.</li>
	 *  <li>History - If an item was in the system at one point in time.</li>
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
		$this->duplicate = array();
		$this->duplicate_count = array();
		$this->duplicate_serials = array();
		$this->history = array();
		$this->missing = array();
		$this->missing_count = array();
		$this->missing_serials = array();
		$this->potential = array();
		$this->invalid = array();
		// Work on a copy.
		$entries = unserialize(serialize($this->entries));
                
                $all_entries = array();
                $products = array();
                foreach ($entries as &$cur_entry) {
                    $product = $pines->com_sales->get_product_by_code($cur_entry->code);
                    $all_entries[$cur_entry->code] = array('entry' => $cur_entry, 'product' => $product);
                    
                    if (isset($product))
                        $products[$product->sku] = array('product' => $product, 'serial' => $cur_entry->code, 'entry' => $cur_entry);
                }
                unset($cur_entry);
                unset($product);
                
                // Create assoc array for expected countsheet
                $locations_stock = (array) $pines->entity_manager->get_entities(
                            array('class' => com_sales_stock),
                            $and_selector,
                            $not_selector,
                            array('&',
                                    'ref' => array('location', $this->group)
                                )
                        );
                
                // Creating a duplicate array of the stock items so that we can manipulate it
                $expected_countsheet = array();
                foreach ($locations_stock as $stock) {
                    $expected_countsheet[$stock->guid] = $stock;
                    if (isset($all_entries[$stock->serial]) && $stock->product->serialized) {
                        // Found the item in our inputted stock
                        // Need to reduce the stock in our expected countsheet and from the user inputted countsheet
                        $this->matched[] = $stock;
                        $this->matched_count[$stock->product->guid]++;
                        $this->matched_serials[$stock->product->guid][] = $stock->serial;
                        $not_selector['guid'][] = $stock->guid;
                        $all_entries[$stock->serial]['entry']->qty--;
                        unset($expected_countsheet[$stock->guid]);
                        
                        // If we don't have any more quantity, take it away
                        if ($all_entries[$stock->serial]->qty <= 0) {
                            unset($all_entries[$stock->serial]);
                            unset($products[$stock->product->sku]);
                        }
                        
                        
                    } elseif (isset($products[$stock->product->sku])) {
                        // Found the item by barcode/sku
                        $product = $products[$stock->product->sku]['product'];
                        if ($product->serialized) {
                            if (!$stock->in_array($this->potential[$product->sku]['closest'])) {
                                $this->potential[$product->sku]['name'] = $product->sku;
                                $this->potential[$product->sku]['closest'][] = $stock;
                            }
                            $this->potential[$product->sku]['count']++;
                            
                        } else {
                            $this->matched[] = $stock;
                            $this->matched_count[$stock->product->guid]++;
                            $this->matched_serials[$stock->product->guid][] = array();
                            $not_selector['guid'][] = $stock->guid;
                            unset($expected_countsheet[$stock->guid]);
                        }
                        $products[$product->sku]['entry']->qty--;
                        
                        if ($products[$product->sku]['entry']->qty <= 0) {
                            unset($all_entries[$products[$product->sku]['serial']]);
                            unset($products[$product->sku]);
                        }
                    }
                }

		// Find entries based on serial.
		foreach ($all_entries as &$cur_entry) {
			if ($cur_entry->qty <= 0)
				continue;
			$stock = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_stock, 'limit' => 5),
					$and_selector,
					$not_selector,
					array('&',
						'strict' => array('serial', $cur_entry->code)
					),
					array('!&',
						'ref' => array('location', $this->group)
					)
				);
			foreach ($stock as $cur_stock) {
				// If the product isn't serialized, something's wrong, don't save it.
				if (!$cur_stock->product->serialized)
					continue;
				if (!$cur_stock->in_array($this->potential[$cur_entry->code]['entries'])) {
					$this->potential[$cur_entry->code]['name'] = $cur_entry->code;
					// Entries, since it's in another location.
					$this->potential[$cur_entry->code]['entries'][] = $cur_stock;
				}
				$this->potential[$cur_entry->code]['count']++;
				$cur_entry->qty--;
			}
		}
		unset($cur_entry);
		// Find entries based on SKU/barcode.
		foreach ($all_entries as &$cur_entry) {
			if ($cur_entry->qty <= 0)
				continue;
                        $product = $all_entries[$cur_entry->code]['product'];
			if (!isset($product))
				continue;
			$stock = (array) $pines->entity_manager->get_entities(
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
			foreach ($stock as $cur_stock) {
				if (!$cur_stock->in_array($this->potential[$cur_entry->code]['entries'])) {
					$this->potential[$cur_entry->code]['name'] = $cur_entry->code;
					// Entries, since it's in another location.
					$this->potential[$cur_entry->code]['entries'][] = $cur_stock;
				}
				$this->potential[$cur_entry->code]['count']++;
				$cur_entry->qty--;
			}
		}

		unset($cur_entry);
		// Check for duplicates.
		foreach ($all_entries as &$cur_entry) {
			if ($cur_entry->qty <= 0)
				continue;
			$found = false;
			foreach ($this->matched as $cur_matched) {
				if ( $cur_matched->product->serialized && (in_array($cur_entry->code, $this->matched_serials[$cur_matched->product->guid])) ) {
					$this->duplicate[] = $cur_matched;
					$this->duplicate_count[$cur_matched->product->guid]++;
					$this->duplicate_serials[$cur_matched->product->guid][] = $cur_matched->serial;
					$found = true;
					$cur_entry->qty--;
				} elseif ( !$cur_matched->product->serialized && ($cur_entry->code == $cur_matched->product->sku) ) {
					$this->duplicate[] = $cur_matched;
					$this->duplicate_count[$cur_matched->product->guid]++;
					$this->duplicate_serials[$cur_matched->product->guid] = array();
					$found = true;
					$cur_entry->qty--;
				}
			}
			if (!$found) {
				$stock_history = (array) $pines->entity_manager->get_entities(
						array('class' => com_sales_stock, 'limit' => 5),
						array('&',
							'strict' => array('serial', $cur_entry->code)
						),
						array('!&',
							'isset' => array('location')
						)
					);
				foreach ($stock_history as $cur_history) {
					$this->history[] = $cur_history;
					$found = true;
					$cur_entry->qty--;
				}
			}
		}
		unset($cur_entry);
		// All the rest are invalid.
		$this->invalid = array();
		foreach ($all_entries as $cur_entry) {
			if ($cur_entry->qty <= 0)
				continue;
			$this->invalid = array_merge($this->invalid, array_fill(0, $cur_entry->qty, $cur_entry->code));
		}
                // Missing entries are the ones left in the $expected_countsheet
                foreach ($expected_countsheet as $missing_stock) {
                    $this->missing[] = $missing_stock;
                    $this->missing_count[$stock->product->guid]++;
                    if ($missing_stock->product->serialized) {
                        $this->missing_serials[$missing_stock->product->guid][] = $missing_stock->serial;
                    } else {
                        $this->missing_serials[$missing_stock->product->guid] = array();
                    }
                }
	}
}

?>