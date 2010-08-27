<?php
/**
 * com_configure_condition class.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A conditional configuration.
 *
 * @package Pines
 * @subpackage com_configure
 */
class com_configure_condition extends entity {
	/**
	 * Load a conditional configuration.
	 * @param int $id The ID of the configuration to load, 0 for a new configuration.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_configure', 'condition');
		// Defaults.
		$this->conditions = array();
		$this->sys_config = array();
		$this->com_config = array();
		$this->is_com_configure_condition = true;
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
	 * @return com_configure_condition The new instance.
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
	 * Delete the condition.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted condition $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the condition.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the condition.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_configure', 'condition_form', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>