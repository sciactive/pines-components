<?php
/**
 * com_modules_module class.
 *
 * @package Pines
 * @subpackage com_modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A module.
 *
 * @package Pines
 * @subpackage com_modules
 */
class com_modules_module extends entity {
	/**
	 * Load a module.
	 * @param int $id The ID of the module to load, 0 for a new module.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_modules', 'module');
		// Defaults.
		$this->enabled = true;
		$this->show_title = true;
		$this->options = array();
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
	 * @return com_modules_module The new instance.
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
	 * Delete the module.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted module $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the module.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the module.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_modules', 'module/form', 'content');
		$module->entity = $this;
		$module->modules = $pines->com_modules->module_types();

		return $module;
	}
}

?>