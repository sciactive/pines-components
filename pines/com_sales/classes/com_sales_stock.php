<?php
/**
 * com_sales_stock class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
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
class com_sales_stock extends entity {
	/**
	 * Load a stock entry.
	 * @param int $id The ID of the stock entry to load, 0 for a new entry.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'stock');
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
	 * Find the PO or transfer that corresponds to an incoming product.
	 *
	 * @todo Go through each matched transfer/PO and check which one has the earliest ETA.
	 * @return array|null An array with the PO, and the stock entry, or null if nothing is found.
	 */
	function inventory_origin() {
		global $pines;
		// Get all the transfers.
		$entities = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'transfer'), 'class' => com_sales_transfer));
		if (!is_array($entities))
			$entities = array();
		// Iterate through all the transfers.
		foreach ($entities as $cur_transfer) {
			// If the transfer isn't for our destination, move on.
			if (!$_SESSION['user']->ingroup($cur_transfer->destination))
				continue;
			if (!is_array($cur_transfer->stock))
				continue;
			// Iterate the transfer's stock, looking for a match.
			foreach ($cur_transfer->stock as $cur_stock) {
				if (is_array($cur_transfer->received)) {
					// If the product is already received, we should ignore it.
					if ($cur_stock->in_array($cur_transfer->received))
						continue;
				}
				// If it's not the right product, move on.
				if ($cur_stock->product->guid != $this->product->guid)
					continue;
				if (!is_null($this->serial) || !is_null($cur_stock->serial)) {
					// Check the serial with the stock entry's serial.
					if ($cur_stock->serial != $this->serial)
						continue;
				}
				// If it's a match, return the transfer and the item.
				return array($cur_transfer, $cur_stock);
			}
		}

		// Get all the POs.
		$entities = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'po'), 'class' => com_sales_po));
		if (!is_array($entities)) {
			$entities = array();
		}
		// Iterate through all the POs.
		foreach ($entities as $cur_po) {
			// If the PO isn't for our destination, move on.
			if (!$_SESSION['user']->ingroup($cur_po->destination))
				continue;
			// If the PO has no products, move on.
			if (!is_array($cur_po->products))
				continue;
			// Iterate the PO's products, looking for a match.
			foreach ($cur_po->products as $cur_product) {
				// If it's not the right product, move on.
				if ($cur_product['entity']->guid != $this->product->guid)
					continue;
				// If the product is already received, we should ignore it.
				$received = 0;
				if (is_array($cur_po->received)) {
					// Count how many of this product has been received.
					foreach ($cur_po->received as $cur_received_stock_entity) {
						if (!is_null($cur_received_stock_entity) && $cur_product['entity']->guid == $cur_received_stock_entity->product->guid) {
							$received++;
						}
					}
				}
				// If we haven't received all of them yet, return the PO and the stock entry (this one).
				if ($received < $cur_product['quantity']) {
					// Fill in some info for this item.
					$this->cost = $cur_product['cost'];
					$this->vendor = $cur_po->vendor;
					return array($cur_po, $this);
				}
			}
		}
		// Nothing found, return null.
		return null;
	}

	/**
	 * Receive the stock entry on a PO/transfer/etc.
	 *
	 * This process creates a transaction entity. It adds itself to the
	 * receiving entity's "received" var. It updates the location of the stock
	 * entry, and sets the status to "available".
	 *
	 * If $location is not set, the current user's primary group is used.
	 *
	 * @param entity $on_entity The entity which the product is to be received on.
	 * @param entity $location The group to use for the new location.
	 * @return bool True on success, false on failure.
	 */
	function receive(&$on_entity = null, $location = false) {
		global $pines;
		if (is_null($on_entity))
			return false;

		// Keep track of the status of the whole process.
		$return = true;
		// Make a transaction entry.
		$tx = com_sales_tx::factory('com_sales', 'transaction', 'stock_tx');

		if ($this->status)
			$old_status = $this->status;
		$this->status = 'available';
		if ($this->location)
			$old_location = $this->location;
		// TODO: Copy location to GID (optional) to allow easier access control.
		$this->location = ($location ? $location : $_SESSION['user']->group);
		if ($on_entity->has_tag('po')) {
			$tx->type = 'received_po';
		} elseif ($on_entity->has_tag('transfer')) {
			$tx->type = 'received_transfer';
		} else {
			$tx->type = 'received_other';
		}
		if (!($this->guid))
			$return = $return && $this->save();

		if (!is_array($on_entity->received))
			$on_entity->received = array();
		$on_entity->received[] = $this;
		$return = $return && $on_entity->save();

		$tx->old_status = $old_status;
		$tx->new_status = $this->status;
		$tx->old_location = $old_location;
		$tx->new_location = $this->location;
		$tx->ref = $on_entity;
		$tx->stock = $this;
		$return = $return && $tx->save();
		return $return;
	}

	/**
	 * Remove the stock entry from available inventory.
	 *
	 * This process creates a transaction entity. It updates the location of the
	 * stock entry, and sets the status to "other" by default.
	 *
	 * If $location is not set, it becomes null, meaning the stock is no longer
	 * located within the company.
	 *
	 * @param entity $on_entity The entity which the product is to be removed by.
	 * @param string $status The new status for the stock. Such as "sold_at_store".
	 * @param entity $location The group to use for the new location.
	 * @return bool True on success, false on failure.
	 */
	function remove(&$on_entity = null, $status = 'other', $location = null) {
		global $pines;
		if (is_null($on_entity))
			return false;

		// Keep track of the status of the whole process.
		$return = true;
		// Make a transaction entry.
		$tx = com_sales_tx::factory('com_sales', 'transaction', 'stock_tx');

		if ($this->status)
			$old_status = $this->status;
		$this->status = (string) $status;
		if ($this->location)
			$old_location = $this->location;
		// TODO: Copy location to GID (optional) to allow easier access control.
		$this->location = $location;
		$tx->type = 'removed';

		// Make sure we have a GUID before saving the tx.
		if (!($this->guid))
			$return = $return && $this->save();

		$tx->old_status = $old_status;
		$tx->new_status = $this->status;
		$tx->old_location = $old_location;
		$tx->new_location = $this->location;
		$tx->ref = $on_entity;
		$tx->stock = $this;
		$return = $return && $tx->save();
		return $return;
	}
}

?>