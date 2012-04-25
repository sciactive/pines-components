<?php
/**
 * com_content_page class.
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
 * A page.
 *
 * @package Components\content
 */
class com_content_page extends entity {
	/**
	 * Load an page.
	 * @param int $id The ID of the page to load, 0 for a new page.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_content', 'page');
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
		$this->title_use_name = true;
		$this->content_tags = array();
		$this->com_menueditor_entries = array();
		$this->conditions = array();
		$this->publish_end = null;
		$this->variants = array();
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
		global $pines;
		// Remove page from categories.
		$cats = $pines->entity_manager->get_entities(
				array('class' => com_content_category, 'skip_ac' => true),
				array('&',
					'tag' => array('com_content', 'category'),
					'ref' => array('pages', $this)
				)
			);
		foreach ($cats as &$cur_cat) {
			while (($key = $this->array_search($cur_cat->pages)) !== false) {
				unset($cur_cat->pages[$key]);
				$cur_cat->pages = array_values($cur_cat->pages);
			}
			if (!$cur_cat->save()) {
				pines_error("Couldn't remove page from category, {$cur_cat->name}.");
				pines_log("Couldn't remove page from category, {$cur_cat->name}.", 'error');
				return false;
			}
		}
		unset($cur_cat);
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
		$categories = (array) $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'tag' => array('com_content', 'category'), 'ref' => array('pages', $this)));
		return $categories;
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
		$config_name = "def_page_$name";
		return $pines->config->com_content->$config_name;
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
		if (!$this->ready())
			return null;
		global $pines;
		$pines->com_content->load_custom_css();
		$module = new module('com_content', 'page/intro', 'content');
		$module->entity = $this;

		return $module;	var_dump($cur_entity->content_tags);
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
					'tag' => array('com_content', 'category'),
					'data' => array('enabled', true)
				)
			);
		if (isset($pines->editor)) {
			foreach ($pines->com_content->get_custom_css() as $cur_file)
				$pines->editor->add_css($cur_file);
		}

		return $module;
	}

	/**
	 * Print the page content.
	 * @return module The page's module.
	 */
	public function print_page() {
		if (!$this->ready())
			return null;
		global $pines;
		$pines->com_content->load_custom_css();
		$module = new module('com_content', 'page/page', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Determine if this page is ready to print.
	 *
	 * This function will check the publish date against today's date. It will
	 * then check the conditions of the page. If the page is disabled, the date
	 * is outside the publish date range, or any of the conditions aren't met,
	 * it will return false.
	 *
	 * @return bool True if the page is ready, false otherwise.
	 */
	public function ready() {
		if (!$this->enabled)
			return false;
		// Check the publish date.
		$time = time();
		if ($this->publish_begin > $time)
			return false;
		if (isset($this->publish_end) && $this->publish_end <= $time)
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