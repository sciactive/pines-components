<?php
/**
 * com_sales_category class.
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
 * A product category.
 *
 * @package Components\sales
 */
class com_sales_category extends entity {
	/**
	 * Load a category.
	 * @param int $id The ID of the category to load, 0 for a new category.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'category');
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
		$this->parent = null;
		$this->children = array();
		$this->products = array();
		$this->show_title = true;
		$this->show_breadcrumbs = true;
		$this->show_products = true;
		$this->show_pages = array();
		$this->menu_position = 'left';
		$this->specs = array();
		$this->title_use_name = true;
		$this->title_position = 'prepend';
		$this->meta_tags = array();
	}

	/**
	 * Create a new instance.
	 * @return com_sales_category The new instance.
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
		global $pines;
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'product category';
			case 'types':
				return 'product categories';
			case 'url_view':
				if ($pines->config->com_sales->com_storefront)
					return pines_url('com_storefront', 'category/browse', array('a' => $this->alias));
				break;
			case 'url_edit':
				if (gatekeeper('com_sales/editcategory'))
					return pines_url('com_sales', 'category/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listcategories'))
					return pines_url('com_sales', 'category/list');
				break;
			case 'icon':
				return 'picon-folder-tar';
		}
		return null;
	}

	/**
	 * Delete the category.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (isset($this->parent)) {
			$key = $this->array_search($this->parent->children);
			if ($key === false) {
				pines_log("Failed to find category in parent {$this->parent->name}.", 'error');
				return false;
			}
			unset($this->parent->children[$key]);
			if (!$this->parent->save()) {
				pines_log("Failed to remove category from parent {$this->parent->name}.", 'error');
				return false;
			}
			unset ($this->parent);
		}
		foreach ($this->children as $cur_child) {
			if (!$cur_child->delete()) {
				pines_log("Failed to delete child category {$cur_child->name}.", 'error');
				$this->save();
				return false;
			}
		}
		if (!parent::delete())
			return false;
		pines_log("Deleted category {$this->guid}.", 'notice');
		return true;
	}

	/**
	 * Get the ancestors of this category.
	 * @return array Ancestor categories.
	 */
	public function get_ancestors() {
		$ancestors = array();
		$cur_cat = $this;
		while (isset($cur_cat->parent->guid)) {
			$ancestors[] = $cur_cat->parent;
			$cur_cat = $cur_cat->parent;
		}
		return $ancestors;
	}

	/**
	 * Get the descendants of this category.
	 * @return array Descendant categories.
	 */
	public function get_descendants() {
		$descendants = array_merge(array(), (array) $this->children);
		foreach ((array) $this->children as $cur_child) {
			if (!isset($cur_child->guid))
				continue;
			$descendants = array_merge($descendants, $cur_child->get_descendants());
		}
		return $descendants;
	}

	/**
	 * Get all specs from ancestors.
	 * @return array Array of specs.
	 */
	public function get_specs_ancestors() {
		$specs = array();
		if (isset($this->parent))
			$specs = $this->parent->get_specs_all();
		return $specs;
	}

	/**
	 * Get all specs from the category and ancestors.
	 * @return array Array of specs.
	 */
	public function get_specs_all() {
		$specs = $this->get_specs_ancestors();
		return array_merge($specs, (array) $this->specs);
	}

	/**
	 * Print a form to edit the category.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'category/form', 'content');
		$module->entity = $this;
		$module->categories = $pines->entity_manager->get_entities(array('class' => com_sales_category), array('&', 'tag' => array('com_sales', 'category'), 'data' => array('parent', null)));

		return $module;
	}
}

?>