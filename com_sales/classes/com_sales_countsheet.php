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
         * @param $run boolean Whether or not to run the count.
	 * @return module The form's module.
	 */
	public function print_form($run = true) {
		global $pines;

		if (!isset($this->group->guid))
			$this->group = $_SESSION['user']->group;
		if ($run)
                    $this->run_count();

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
                
                // Instead of the 6 foreach loops, run one with if statments
                foreach($entries as &$cur_entry) {
                    // Need to check to make sure we have more than 0 in the quantity
                    if ($cur_entry->qty <= 0)
                        continue;
                    
                    $stock = (array) $pines->entity_manager->get_entities(
                                    array('class' => com_sales_stock),
                                    $and_selector,
                                    $not_selector
                            );
                    
                    foreach ($stock as $cur_stock) {
                        
                        // Find entries based on location and serial
                        if ($cur_stock->serial == $cur_entry->code && $cur_stock->location == $this->group && $cur_stock->product_serialized) {
                            $this->matched[] = $cur_stock;
                            $this->matched_count[$cur_stock->product->guid]++;
                            $this->matched_serials[$cur_stock->product->guid][] = $cur_stock->serial;
                            $cur_entry->qty--;
                            // Going to continue because if it hits this first if check above, then we only want to mess with it inside this block
                            // This will be the same for the others
                            $not_selector['guid'][] = $cur_stock->guid;
                            continue;
                        }
                        
                        // If we made it this far, get the $product for the later checks
                        $product = $pines->com_sales->get_product_by_code($cur_entry->code);
                        
                        // Find entries based on location and SKU/barcode
                        if ($cur_stock->location == $this->location && isset($product) && $cur_stock->product == $product) {
                            // Second round of blocks
                            if ($product->serialized) {
                                if (!$cur_stock->in_array($this->potential[$cur_entry->code]['closest'])) {
                                    $this->potential[$cur_entry->code]['name'] = $cur_entry->code;
                                    // Comment already here: Closest, since it's in this location
                                    $this->potential[$cur_entry->code]['closest'][] = $cur_stock;
                                }
                                $this->potential[$cur_entry->code]['count']++;
                                // Included the $cur_entry here since the original code would decrement $cur_entry regardless if the if was true/false
                                $cur_entry->qty--;
                                
                            } else {
                                $this->matched[] = $cur_stock;
                                $this->matched_count[$cur_stock->product->guid]++;
                                $this->matched_serials[$cur_stock->product->guid] = array();
                                // Decrease $cur_entry->qty-- since we need to do it for both the cases but we need to continue if it is not serialized
                                $cur_entry->qty--;
                                // We continue because in the original code, this block would remove the guid, indicating that it should move on to the next one
                                $not_selector['guid'][] = $cur_stock->guid;
                                continue;
                            }
                        }
                        
                        // Find entries based on serial
                        if ($cur_stock->serial == $cur_entry->code && $cur_stock->location != $this->group && $cur_stock->product->serialized) {
                            
                            if (!$cur_stock->in_array($this->potential[$cur_entry->code]['entries'])) {
                                $this->potential[$cur_entry->code]['name'] = $cur_entry->code;
                                // Original comment: Entries, since it's in another location
                                $this->potential[$cur_entry->code]['entries'][] = $cur_stock;
                            }
                            $this->potential[$cur_entry->code]['count']++;
                            $cur_entry->qty--;
                            // Don't continue onto the next one since we never remove guid in the original code
                            //continue;
                        }
                        
                        // Find entries based on SKU/barcode
                        if (isset($product) && $cur_stock->product == $product && $cur_stock->location != $this->group) {
                            if (!$cur_stock->in_array($this->potential[$cur_entry->code]['entries'])) {
                                $this->potential[$cur_entry->code]['name'] = $cur_entry->code;
                                // Original comment: Entries, since it's in antoher location
                                $this->potential[$cur_entry->code]['entries'][] = $cur_stock;
                            }
                            $this->potential[$cur_entry->code]['count']++;
                            $cur_entry->qty--;
                            // Don't continue here since we never remove the guid in the original code
                            //continue;
                        }
                        
                    }
                    
                    // Check for duplicates
                   
                    $found = false;
                    foreach ($this->matched as $cur_matched) {
                        if (($cur_matched->product->serialized && (in_array($cur_entry->code, $this->matched_serials[$cur_matched->product->guid]))) || (!$cur_matched->product->serialized && ($cur_entry->code == $cur_matched->product->sku))) {
                            $this->duplicate[] = $cur_matched;
                            $this->duplicate_count[$cur_matched->product->guid]++;
                            $this->duplicate_serials[$cur_matched->product->guid][] = $cur_matched->serial;
                            $found = true;
                            $cur_entry->qty--;
                        }
                    }
                    
                    if (!$found) {
                        $stock_history = (array) $pines->entity_manager->get_entities(
                                array('class' => com_sales_stock, 'limit' => 5),
                                array('&',
                                        'strict' => array('serial', $cur_entry->code)),
                                array('!&',
                                        'isset' => array('location'))
                                );
                        foreach ($stock_history as $cur_history) {
                            $this->history[] = $cur_history;
                            $found = true;
                            $cur_entry->qty--;
                        }
                    }
                   unset($cur_entry);
                }
                
                // Get the rest of the invalids
                $this->invalid = array();
                foreach ($entries as $cur_entry) {
                    if ($cur_entry->qty <= 0)
                        continue;
                    
                    $this->invalid = array_merge($this->invalid, array_fill(0, $cur_entry->qty, $cur_entry->code));
                    
                    // Find entries that should be counted, but weren't found
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
                            $this->missing_serials[$stock->product->guid][] = array();
                        }
                    }
                }
	}
}

?>