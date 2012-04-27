<?php
/**
 * com_content_category class.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A category.
 *
 * @package Components\content
 */
class com_content_category extends entity {
	/**
	 * Load a category.
	 * @param int $id The ID of the category to load, 0 for a new category.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_content', 'category');
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
		// Defaults
		$this->enabled = true;
		$this->title_use_name = true;
		$this->parent = null;
		$this->children = array();
		$this->pages = array();
		$this->com_menueditor_entries = array();
		$this->conditions = array();
		$this->variants = array();
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

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'content category';
			case 'types':
				return 'content categories';
			case 'url_view':
				return pines_url('com_content', 'category', array('a' => $this->alias));
			case 'url_edit':
				if (gatekeeper('com_content/editcategory'))
					return pines_url('com_content', 'category/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_content/listcategories'))
					return pines_url('com_content', 'category/list');
				break;
			case 'icon':
				return 'picon-folder-html';
		}
		return null;
	}

	/**
	 * Delete the category.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (isset($this->parent)) {
			$i = $this->array_search($this->parent->children);
			unset($this->parent->children[$i]);
			if (!$this->parent->save()) {
				pines_error("Couldn't remove category from parent {$this->parent->name}.");
				pines_log("Couldn't remove category from parent {$this->parent->name}.", 'error');
				return false;
			}
			unset($this->parent);
		}
		foreach ($this->children as $cur_child) {
			if (!$cur_child->delete()) {
				pines_error("Failed to delete child category {$cur_child->name}.");
				pines_log("Failed to delete child category {$cur_child->name}.", 'error');
				return false;
			}
		}
		if (!parent::delete())
			return false;
		pines_log("Deleted category {$this->name} [{$this->alias}].", 'notice');
		return true;
	}

	/**
	 * Get an option if it's set, the default otherwise.
	 * @param string $name The name of the option.
	 * @return mixed The value.
	 */
	public function get_option($name) {
		if (isset($this->$name))
			return $this->$name;
		global $pines;
		$config_name = "def_cat_$name";
		return $pines->config->com_content->$config_name;
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