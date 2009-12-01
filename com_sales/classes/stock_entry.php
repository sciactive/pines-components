<?php
/**
 * stock_entry class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An entry of a product into stock.
 *
 * @package Pines
 * @subpackage com_sales
 */
class stock_entry extends entity {
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_sales', 'stock_entry');
	}

	/**
	 * Find the PO or transfer that corresponds to an incoming product.
	 *
	 * @return array|null An array with the PO, and the product entry, or null if nothing is found.
	 */
	function inventory_origin() {
		global $config;
		// Get all the POs.
		$entities = $config->entity_manager->get_entities_by_tags('com_sales', 'po');
		if (!is_array($entities)) {
			$entities = array();
		}
		// Iterate through all the POs.
		foreach ($entities as $cur_po) {
			if (!is_array($cur_po->products))
				continue;
			// Iterate the PO's products, looking for a match.
			foreach ($cur_po->products as $cur_product) {
				// If it's not the right product, move on.
				if ($cur_product->guid != $this->product_guid)
					continue;
				// If the product is already received, we should ignore it.
				$received = 0;
				if (is_array($cur_po->received)) {
					// Count how many of this product has been received.
					foreach ($cur_po->received as $cur_received) {
						$cur_received_stock_entity = $config->entity_manager->get_entity($cur_received, array('com_sales', 'stock_entry'), stock_entry);
						if (!is_null($cur_received_stock_entity) && $cur_product->guid == $cur_received_stock_entity->product_guid) {
							$received++;
						}
					}
				}
				// If we haven't received all of them yet, return the PO and the item.
				if ($received < $cur_product->quantity) {
					return array($cur_po, $cur_product);
				}
			}
		}
		// Nothing found, return null.
		return null;
	}

	/**
	 * Receive the stock entry on a PO/transfer/etc.
	 *
	 * This process creates a transaction entity. It adds its GUID to the
	 * receiving entity's "received" var. It updates the location of the stock
	 * entry, and sets the status to "available".
	 *
	 * If $location is not set, the current user's primary group is used.
	 *
	 * @param entity $on_entity The entity which the product is to be received on.
	 * @param int $location The GUID of the group to use for the new location.
	 * @return bool True on success, false on failure.
	 */
	function receive(&$on_entity = null, $location = false) {
		if (is_null($on_entity))
			return false;

		// Keep track of the status of the whole process.
		$return = true;
		// Make a transaction entry.
		$tx = new entity('com_sales', 'transaction', 'stock_tx');

		if ($this->status)
			$old_status = $this->status;
		$this->status = 'available';
		if ($this->location)
			$old_location = $this->location;
		$this->location = $location ? $location : $_SESSION['user']->gid;
		if ($on_entity->has_tag('po')) {
			$tx->type = 'received_po';
		} elseif ($on_entity->has_tag('transfer')) {
			$tx->type = 'received_transfer';
		} elseif ($on_entity->has_tag('refund')) {
			$tx->type = 'received_refund';
		} else {
			$tx->type = 'received_other';
		}
		if (!($this->guid))
			$return = $return && $this->save();

		if (!is_array($on_entity->received))
			$on_entity->received = array();
		$on_entity->received[] = $this->guid;
		$return = $return && $on_entity->save();
		
		$tx->old_status = $old_status;
		$tx->new_status = $this->status;
		$tx->old_location = $old_location;
		$tx->new_location = $this->location;
		$tx->ref_guid = $on_entity->guid;
		$tx->stock_guid = $this->guid;
		$return = $return && $tx->save();
		return $return;
	}
}

?>