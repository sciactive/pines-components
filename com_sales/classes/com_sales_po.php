<?php
/**
 * com_sales_po class.
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
 * A PO.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_po extends entity {
	/**
	 * Load a PO.
	 * @param int $id The ID of the PO to load, 0 for a new PO.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'po');
		// Defaults.
		$this->products = array();
		$this->finished = false;
		$this->destination = $_SESSION['user']->group;
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
	 * @return com_sales_po The new instance.
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
	 * Delete the PO.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		// Don't delete the PO if it has received items.
		if (!empty($this->received))
			return false;
		if (!parent::delete())
			return false;
		pines_log("Deleted PO $this->po_number.", 'notice');
		return true;
	}

	/**
	 * Save the PO.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->po_number) || !$this->products)
			return false;
		if (!$this->finished) {
			$this->pending_products = array();
			$this->pending_serials = array();
			foreach ($this->products as &$cur_product) {
				$cur_product['received'] = 0;
				// Count how many of this product has been received.
				foreach ((array) $this->received as $cur_received_stock_entity) {
					if (isset($cur_received_stock_entity) && $cur_product['entity']->is($cur_received_stock_entity->product))
						$cur_product['received']++;
				}
				// If we've received all of them, move on.
				if ($cur_product['received'] >= $cur_product['quantity'])
					continue;
				$this->pending_products[] = $cur_product['entity'];
			}
			unset($cur_product);
			if (empty($this->pending_products))
				$this->finished = true;
		}
		return parent::save();
	}

	/**
	 * Print a form to edit the PO.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'po/form', 'content');
		$module->entity = $this;
		$module->locations = (array) $pines->user_manager->get_groups();
		$module->shippers = (array) $pines->entity_manager->get_entities(array('class' => com_sales_shipper), array('&', 'tag' => array('com_sales', 'shipper')));
		$module->vendors = (array) $pines->entity_manager->get_entities(array('class' => com_sales_vendor), array('&', 'tag' => array('com_sales', 'vendor')));
		$module->products = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_product),
				array('&',
					'data' => array('enabled', true),
					'tag' => array('com_sales', 'product')
				)
			);
		
		return $module;
	}
}

?>