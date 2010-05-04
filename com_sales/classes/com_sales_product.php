<?php
/**
 * com_sales_product class.
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
 * A product.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_product extends entity {
	/**
	 * Load a product.
	 * @param int $id The ID of the product to load, 0 for a new product.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'product');
		// Defaults.
		$this->enabled = true;
		$this->additional_tax_fees = array();
		$this->serialized = true;
		$this->discountable = true;
		$this->require_customer = true;
		$this->additional_barcodes = array();
		$this->actions = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (!isset($entity))
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
	 * Delete the product.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted product $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the product.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the product.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'form_product', 'content');
		$module->entity = $this;
		$module->manufacturers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'manufacturer'), 'class' => com_sales_manufacturer));
		if (!is_array($module->manufacturers))
			$module->manufacturers = array();
		$module->vendors = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'vendor'), 'class' => com_sales_vendor));
		if (!is_array($module->vendors))
			$module->vendors = array();
		$module->tax_fees = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'tax_fee'), 'class' => com_sales_tax_fee));
		if (!is_array($module->tax_fees))
			$module->tax_fees = array();
		$module->actions = (array) $pines->config->com_sales->product_actions;
		if (!is_array($module->actions))
			$module->actions = array();

		return $module;
	}
}

?>