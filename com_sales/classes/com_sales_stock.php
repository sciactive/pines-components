<?php
/**
 * com_sales_stock class.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An entry of a product into stock.
 *
 * @package Components\sales
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
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		$this->location = null;
		$this->ac = (object) array('user' => 2, 'group' => 2, 'other' => 2);
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
	 * Return the entity helper module.
	 * @return module Entity helper module.
	 */
	public function helper() {
		return new module('com_sales', 'stock/helper');
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return "Stock Entry $this->guid";
			case 'type':
				return 'stock entry';
			case 'types':
				return 'stock entries';
			case 'url_edit':
				if (gatekeeper('com_sales/managestock'))
					return pines_url('com_sales', 'stock/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/managestock') || gatekeeper('com_sales/seestock'))
					return pines_url('com_sales', 'stock/list');
				break;
			case 'icon':
				return 'picon-package-x-generic';
		}
		return null;
	}

	/**
	 * Get the reason from the last stock transaction.
	 * @return string The reason, or "unknown" if no last transaction could be found.
	 */
	public function last_reason() {
		global $pines;
		$last_tx = $pines->entity_manager->get_entity(
				array('reverse' => true, 'class' => com_sales_tx),
				array('&',
					'tag' => array('com_sales', 'transaction', 'stock_tx'),
					'ref' => array('stock', $this)
				)
			);
		if (isset($last_tx)) {
			return $last_tx->reason;
		} else {
			return 'unknown';
		}
	}

	/**
	 * Print a form to edit the stock entry.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'stock/form', 'content');
		$module->entity = $this;
		$module->vendors = (array) $pines->entity_manager->get_entities(array('class' => com_sales_vendor), array('&', 'tag' => array('com_sales', 'vendor')));
		$module->locations = $pines->user_manager->get_groups();

		return $module;
	}

	/**
	 * Receive the stock entry on a PO/transfer/return/etc.
	 *
	 * This process creates a transaction entity. It adds itself to the
	 * receiving entity's "received" var if $update_received is true. It updates
	 * the location of the stock entry, and sets the available variable to true.
	 *
	 * If $location is not set, the current user's primary group is used.
	 * 
	 * This does not necessarily save the entity, so be sure to save it if
	 * needed.
	 *
	 * @param string $reason The reason for the stock receipt. Such as "received_po".
	 * @param entity &$on_entity The entity which the product is to be received on.
	 * @param group $location The group to use for the new location.
	 * @param bool $update_received Add this stock entry to $on_entity's received variable.
	 * @return bool True on success, false on failure.
	 */
	public function receive($reason = 'other', &$on_entity = null, $location = null, $update_received = true) {
		if (!in_array($reason, array('received_po', 'received_transfer', 'sale_voided', 'sale_returned', 'sale_swapped', 'sale_removed', 'other')))
			return false;

		// Keep track of the status of the whole process.
		$return = true;
		// Make a transaction entry.
		$tx = com_sales_tx::factory('stock_tx');

		$old_available = (bool) $this->available;
		$this->available = true;
		if ($this->location)
			$old_location = $this->location;
		// TODO: Copy location to group (optional) to allow easier access control.
		$this->location = ($location ? $location : $_SESSION['user']->group);
		$tx->type = 'received';
		$tx->reason = $reason;
		if (isset($on_entity) && $on_entity->has_tag('po')) {
			// If it's being received on a PO, it needs the cost from it.
			if (!$on_entity->pending && $on_entity->save() && !$on_entity->pending) {
				// If it doesn't have a pending array, it's not expecting products.
				pines_log("Receiving stock on a PO that is not expecting products. (PO: $on_entity->po_number)", 'warning');
			} else {
				// Find this product.
				foreach ($on_entity->pending as $cur_pending) {
					if (!$this->product->is($cur_pending['entity']))
						continue;
					$this->cost = (float) $cur_pending['cost'];
					break;
				}
			}
		}
		if (!isset($this->guid))
			$return = $this->save() && $return;

		$tx->old_available = $old_available;
		$tx->new_available = $this->available;
		$tx->old_location = $old_location;
		$tx->new_location = $this->location;
		if (isset($on_entity)) {
			if ($update_received) {
				if ((array) $on_entity->received !== $on_entity->received)
					$on_entity->received = array();
				$on_entity->received[] = $this;
				$return = $on_entity->save() && $return;
			}
			$tx->ref = $on_entity;
		}
		$tx->stock = $this;
		$return = $tx->save() && $return;
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
	 * This does not necessarily save the entity, so be sure to save it if
	 * needed.
	 *
	 * @param string $reason The reason for the stock removal. Such as "sold_at_store".
	 * @param entity &$on_entity The entity which the product is to be removed by.
	 * @param group $location The group to use for the new location.
	 * @return bool True on success, false on failure.
	 */
	public function remove($reason = 'other', &$on_entity = null, $location = null) {
		if (!in_array($reason, array('sold_at_store', 'sold_pending_shipping', 'sold_pending_pickup', 'sold_swapped', 'sale_shipped', 'transfer_shipped', 'other')))
			return false;

		// Keep track of the status of the whole process.
		$return = true;
		// Make a transaction entry.
		$tx = com_sales_tx::factory('stock_tx');

		$old_available = (bool) $this->available;
		$this->available = false;
		if ($this->location)
			$old_location = $this->location;
		// TODO: Copy location to group (optional) to allow easier access control.
		$this->location = $location;
		$tx->type = 'removed';
		$tx->reason = $reason;

		// Make sure we have a GUID before saving the tx.
		if (!isset($this->guid))
			$return = $this->save() && $return;

		$tx->old_available = $old_available;
		$tx->new_available = $this->available;
		$tx->old_location = $old_location;
		$tx->new_location = $this->location;
		if (isset($on_entity))
			$tx->ref = $on_entity;
		$tx->stock = $this;
		$return = $tx->save() && $return;
		return $return;
	}

	/**
	 * Save the stock.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $pines;

		if ($pines->config->com_sales->unique_serials && !empty($this->serial)) {
			$test = $pines->entity_manager->get_entity(array('class' => com_sales_stock, 'skip_ac' => true), array('&', 'tag' => array('com_sales', 'stock'), 'strict' => array('serial', $this->serial), '!guid' => $this->guid));
			if (isset($test)) {
				pines_notice('There is already a stock entry with that serial. Serials must be unique.');
				return false;
			}
		}

		return parent::save();
	}
}

?>