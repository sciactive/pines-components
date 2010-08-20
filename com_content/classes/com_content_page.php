<?php
/**
 * com_content_page class.
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
 * An page.
 *
 * @package Pines
 * @subpackage com_content
 */
class com_content_page extends entity {
	/**
	 * Load an page.
	 * @param int $id The ID of the page to load, 0 for a new page.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_content', 'page');
		// Defaults.
		$this->enabled = true;
		$this->content_tags = array();
		$this->show_title = true;
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
	 * @return com_content_page The new instance.
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
	 * Delete the page.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted page $this->name.", 'notice');
		return true;
	}

	/**
	 * Get an array of categories' GUIDs this page belongs to.
	 * @return array An array of GUIDs.
	 */
	public function get_categories_guid() {
		$categories = $this->get_categories($page);
		foreach ($categories as &$cur_cat) {
			$cur_cat = $cur_cat->guid;
		}
		unset($cur_cat);
		return $categories;
	}

	/**
	 * Get an array of categories this page belongs to.
	 * @return array An array of categories.
	 */
	public function get_categories() {
		global $pines;
		$categories = (array) $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'ref' => array('pages', $this), 'tag' => array('com_content', 'category')));
		return $categories;
	}

	/**
	 * Save the page.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print the page intro.
	 * @return module The form's module.
	 */
	public function print_intro() {
		global $pines;
		$module = new module('com_content', 'page/intro', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form to edit the page.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_content', 'page/form', 'content');
		$module->entity = $this;
		$module->categories = (array) $pines->entity_manager->get_entities(
				array('class' => com_content_category),
				array('&',
					'data' => array('enabled', true),
					'tag' => array('com_content', 'category')
				)
			);

		return $module;
	}

	/**
	 * Print the page content.
	 * @return module The page's module.
	 */
	public function print_page() {
		global $pines;
		$module = new module('com_content', 'page/page', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>