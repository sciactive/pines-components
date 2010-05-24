<?php
/**
 * com_sales_category class.
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
 * A product category.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_category extends entity {
	/**
	 * Load a category.
	 * @param int $id The ID of the category to load, 0 for a new category.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'category');
		// Defaults
		$this->parent = null;
		$this->children = array();
		$this->products = array();
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
	 * Delete the category.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		foreach ($this->children as $cur_child) {
			if (!$cur_child->delete()) {
				pines_log("Failed to delete child category {$cur_child->name}.", 'error');
				return false;
			}
		}
		if (!parent::delete())
			return false;
		pines_log("Deleted category {$this->guid}.", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the category.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'form_category', 'content');
		$module->entity = $this;
		$module->categories = $pines->entity_manager->get_entities(array('data' => array('parent' => null), 'tags' => array('com_sales', 'category'), 'class' => com_sales_category));

		return $module;
	}
}

?>