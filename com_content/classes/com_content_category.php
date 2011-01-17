<?php
/**
 * com_content_category class.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An page category.
 *
 * @package Pines
 * @subpackage com_content
 */
class com_content_category extends entity {
	/**
	 * Load a category.
	 * @param int $id The ID of the category to load, 0 for a new category.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_content', 'category');
		// Defaults
		$this->enabled = true;
		$this->parent = null;
		$this->children = array();
		$this->pages = array();
		$this->menu_position = 'left';
		$this->show_breadcrumbs = true;
		$this->conditions = array();
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
	 * @return com_content_category The new instance.
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
	 * Print the category browser.
	 * @return module The category's module.
	 */
	public function print_category() {
		if (!$this->ready())
			return null;
		global $pines;
		$module = new module('com_content', 'category/category', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form to edit the category.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_content', 'category/form', 'content');
		$module->entity = $this;
		$module->categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'tag' => array('com_content', 'category'), 'data' => array('parent', null)));

		return $module;
	}

	/**
	 * Determine if this category is ready to print.
	 *
	 * This function will check the conditions of the category. If the category
	 * is disabled or any of the conditions aren't met, it will return false.
	 *
	 * @return bool True if the category is ready, false otherwise.
	 */
	public function ready() {
		if (!$this->enabled)
			return false;
		if (!$this->conditions)
			return true;
		global $pines;
		// Check that all conditions are met.
		foreach ($this->conditions as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value))
				return false;
		}
		return true;
	}
}

?>