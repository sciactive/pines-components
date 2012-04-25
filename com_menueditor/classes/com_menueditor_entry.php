<?php
/**
 * com_menueditor_entry class.
 *
 * @package Components
 * @subpackage menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An entry.
 *
 * @package Components
 * @subpackage menueditor
 */
class com_menueditor_entry extends entity {
	/**
	 * Load an entry.
	 * @param int $id The ID of the entry to load, 0 for a new entry.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_menueditor', 'entry');
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
		$this->top_menu = 'main_menu';
	}

	/**
	 * Create a new instance.
	 * @return com_menueditor_entry The new instance.
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
	 * Delete the entry.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted menu entry $this->name.", 'notice');
		return true;
	}

	/**
	 * Build and return a menu array to go in the menu service.
	 * @return array The menu entry array.
	 */
	public function menu_array() {
		if (isset($this->top_menu)) {
			$array = array(
				'path' => $this->location.'/'.$this->name,
				'text' => $this->text
			);
		} else {
			$array = array(
				'path' => $this->name,
				'text' => $this->text,
				'position' => $this->position
			);
		}
		if ($this->sort)
			$array['sort'] = true;
		if (!empty($this->link))
			$array['href'] = $this->link;
		if (!empty($this->onclick))
			$array['onclick'] = $this->onclick;
		$depend = $this->conditions;
		if ($this->children)
			$depend['children'] = true;
		$array['depend'] = $depend;
		return $array;
	}

	/**
	 * Save the entry.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the entry.
	 * 
	 * @param bool $override_page Whether to override the page with the output of the module. (This is needed because the menu arrays aren't available until after the kill scripts.)
	 * @return module The form's module.
	 */
	public function print_form($override_page = false) {
		global $pines;
		$module = new module('com_menueditor', 'entry/form', 'content');
		$module->entity = $this;
		// Set up a hook to capture the menu entries before they get destroyed.
		if ($override_page)
			$callback = array($this, 'capture_menu_override');
		else
			$callback = array($this, 'capture_menu');
		$pines->hook->add_callback('$pines->menu->render', -1, $callback);
		$this->cur_module = $module;

		return $module;
	}

	/**
	 * Capture the menu entries before they are destroyed.
	 */
	public function capture_menu() {
		global $pines;
		$this->cur_module->captured_menu_arrays = $pines->menu->menu_arrays;
	}

	/**
	 * Capture the menu entries before they are destroyed.
	 * 
	 * Then override the page.
	 */
	public function capture_menu_override() {
		global $pines;
		$this->cur_module->captured_menu_arrays = $pines->menu->menu_arrays;
		$pines->page->override = true;
		$pines->page->override_doc($this->cur_module->render());
	}
}

?>