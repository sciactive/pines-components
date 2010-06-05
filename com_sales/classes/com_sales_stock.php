<?php
/**
 * com_sales_stock class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
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
		$this->ac = (object) array('user' => 2, 'group' => 2, 'other' => 2);
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 * @return com_sales_stock The new instance.
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
	 * Receive the stock entry on a PO/transfer/return/etc.
	 *
	 * This process creates a transaction entity. It adds itself to the
	 * receiving entity's "received" var. It updates the location of the stock
	 * entry, and sets the status to "available".
	 *
	 * If $location is not set, the current user's primary group is used.
	 *
	 * @param string $reason The reason for the stock receipt. Such as "received_po".
	 * @param entity &$on_entity The entity which the product is to be received on.
	 * @param entity $location The group to use for the new location.
	 * @return bool True on success, false on failure.
	 */
	function receive($reason = 'other', &$on_entity = null, $location = null) {
		global $pines;
		if (!in_array($reason, array('received_po', 'received_transfer', 'sale_voided', 'sale_returned', 'other')))
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
		// TODO: Copy location to group (optional) to allow easier access control.
		$this->location = ($location ? $location : $_SESSION['user']->group);
		$tx->type = 'received';
		$tx->reason = $reason;
		if (!($this->guid))
			$return = $return && $this->save();

		if (!isset($on_entity)) {
			if ((array) $on_entity->received !== $on_entity->received)
				$on_entity->received = array();
			$on_entity->received[] = $this;
			$return = $return && $on_entity->save();
		}

		$tx->old_status = $old_status;
		$tx->new_status = $this->status;
		$tx->old_location = $old_location;
		$tx->new_location = $this->location;
		if (isset($on_entity))
			$tx->ref = $on_entity;
		$tx->stock = $this;
		$return = $return && $tx->save();
		return $return;
	}

	/**
	 * Remove the stock entry from available inventory.
	 *
	 * This process creates a transaction entity. It updates the location of the
	 * stock entry, and sets the status to "unavailable".
	 *
	 * If $location is not set, it becomes null, meaning the stock is no longer
	 * located within the company.
	 *
	 * @param string $reason The reason for the stock removal. Such as "sold_at_store".
	 * @param entity &$on_entity The entity which the product is to be removed by.
	 * @param entity $location The group to use for the new location.
	 * @return bool True on success, false on failure.
	 */
	function remove($reason = 'other', &$on_entity = null, $location = null) {
		global $pines;
		if (!in_array($reason, array('sold_at_store', 'sold_pending', 'other')))
			return false;

		// Keep track of the status of the whole process.
		$return = true;
		// Make a transaction entry.
		$tx = com_sales_tx::factory('com_sales', 'transaction', 'stock_tx');

		if ($this->status)
			$old_status = $this->status;
		$this->status = 'unavailable';
		if ($this->location)
			$old_location = $this->location;
		// TODO: Copy location to GID (optional) to allow easier access control.
		$this->location = $location;
		$tx->type = 'removed';
		$tx->reason = $reason;

		// Make sure we have a GUID before saving the tx.
		if (!($this->guid))
			$return = $return && $this->save();

		$tx->old_status = $old_status;
		$tx->new_status = $this->status;
		$tx->old_location = $old_location;
		$tx->new_location = $this->location;
		if (isset($on_entity))
			$tx->ref = $on_entity;
		$tx->stock = $this;
		$return = $return && $tx->save();
		return $return;
	}
}

?>