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
/* @var $pines pines */
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
		$this->enabled = true;
		$this->images = array();
		$this->additional_tax_fees = array();
		$this->return_checklists = array();
		$this->serialized = true;
		$this->discountable = true;
		$this->require_customer = true;
		$this->additional_barcodes = array();
		$this->actions = array();
		$this->show_in_storefront = false;
		$this->title_use_name = true;
		$this->title_position = 'prepend';
		$this->meta_tags = array();
	}

	/**
	 * Create a new instance.
	 * @return com_sales_product The new instance.
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
	 * Get an array of categories' GUIDs this product belongs to.
	 * @return array An array of GUIDs.
	 */
	public function get_categories_guid() {
		$categories = $this->get_categories($product);
		foreach ($categories as &$cur_cat) {
			$cur_cat = $cur_cat->guid;
		}
		unset($cur_cat);
		return $categories;
	}

	/**
	 * Get an array of categories this product belongs to.
	 * @return array An array of categories.
	 */
	public function get_categories() {
		global $pines;
		$categories = (array) $pines->entity_manager->get_entities(array('class' => com_sales_category), array('&', 'tag' => array('com_sales', 'category'), 'ref' => array('products', $this)));
		return $categories;
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
		$module = new module('com_sales', 'product/form', 'content');
		$module->entity = $this;
		$module->categories = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_category),
				array('&',
					'tag' => array('com_sales', 'category'),
					'data' => array('enabled', true)
				)
			);
		$module->manufacturers = (array) $pines->entity_manager->get_entities(array('class' => com_sales_manufacturer), array('&', 'tag' => array('com_sales', 'manufacturer')));
		$pines->entity_manager->sort($module->manufacturers, 'name');
		$module->vendors = (array) $pines->entity_manager->get_entities(array('class' => com_sales_vendor), array('&', 'tag' => array('com_sales', 'vendor')));
		$module->tax_fees = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_tax_fee),
				array('&',
					'tag' => array('com_sales', 'tax_fee'),
					'data' => array('enabled', true)
				)
			);
		$module->return_checklists = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_return_checklist),
				array('&',
					'tag' => array('com_sales', 'return_checklist'),
					'data' => array('enabled', true)
				)
			);
		$module->actions = (array) $pines->config->com_sales->product_actions;
		if ($pines->config->com_sales->com_hrm) {
			$module->groups = (array) $pines->user_manager->get_groups();
			usort($module->groups, array($this, 'sort_groups'));
		}
		
		return $module;
	}

	/**
	 * Sort groups.
	 * @param group $a Group.
	 * @param group $b Group.
	 * @return bool Group order.
	 */
	private function sort_groups($a, $b) {
		$aname = empty($a->name) ? $a->groupname : $a->name;
		$bname = empty($b->name) ? $b->groupname : $b->name;
		return strtolower($aname) > strtolower($bname);
	}
}

?>