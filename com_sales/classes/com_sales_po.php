<?php
/**
 * com_sales_po class.
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
 * A PO.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_po extends entity {
	/**
	 * Load a PO.
	 * @param int $id The ID of the PO to load, null for a new PO.
	 */
	public function __construct($id = null) {
		parent::__construct();
		$this->add_tag('com_sales', 'po');
		if (!is_null($id)) {
			global $config;
			$entity = $config->entity_manager->get_entity($id, $this->tags, get_class($this));
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->parent = $entity->parent;
			$this->tags = $entity->tags;
			$this->entity_cache = array();
			$this->put_data($entity->get_data());
		}
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
		if (!isset($this->po_number))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the PO.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $config;
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_sales', 'form_po', 'content');
		$module->entity = $this;
		$module->locations = $config->user_manager->get_group_array();
		$module->shippers = $config->entity_manager->get_entities_by_tags('com_sales', 'shipper', com_sales_shipper);
		if (!is_array($module->shippers)) {
			$module->shippers = array();
		}
		$module->vendors = $config->entity_manager->get_entities_by_tags('com_sales', 'vendor', com_sales_vendor);
		if (!is_array($module->vendors)) {
			$module->vendors = array();
		}
		$module->products = $config->entity_manager->get_entities_by_tags('com_sales', 'product', com_sales_product);
		if (!is_array($module->products)) {
			$module->products = array();
		}

		return $module;
	}
}

?>