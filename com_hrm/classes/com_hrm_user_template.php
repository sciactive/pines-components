<?php
/**
 * com_hrm_user_template class.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A user template.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_user_template extends entity {
	/**
	 * Load a user template.
	 * @param int $id The ID of the user template to load, 0 for a new user template.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'user_template');
		// Defaults
		$this->groups = array();
		$this->default_component = 'com_hrm';
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 * @return com_hrm_user_template The new instance.
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
	 * Delete the user template.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted user template $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the user template.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the user template.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_hrm', 'form_user_template', 'content');
		$module->entity = $this;
		$module->group_array = $pines->user_manager->get_group_array();
		$module->default_components = $pines->config->get_default_components();

		return $module;
	}
}

?>