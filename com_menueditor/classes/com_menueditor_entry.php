<?php
/**
 * com_menueditor_entry class.
 *
 * @package Pines
 * @subpackage com_menueditor
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
 * @package Pines
 * @subpackage com_menueditor
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
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_menueditor', 'entry/form', 'content');
		$module->entity = $this;
		// Set up a hook to capture the menu entries before they get destroyed.
		$pines->hook->add_callback('$pines->menu->render', -1, array($this, 'capture_menu'));
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
}

?>