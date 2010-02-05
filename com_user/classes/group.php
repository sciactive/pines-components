<?php
/**
 * group class.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Pines system groups.
 *
 * @package Pines
 * @subpackage com_user
 */
class group extends able_entity {
	/**
	 * Load a group.
	 * @param int $id The ID of the group to load, 0 for a new group.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_user', 'group');
		// Defaults.
		$this->abilities = array();
		$this->address_type = 'us';
		if ($id > 0 || is_string($id)) {
			global $pines;
			if (is_int($id)) {
				$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			} else {
				$entity = $pines->entity_manager->get_entity(array('data' => array('groupname' => $id), 'tags' => $this->tags, 'class' => get_class($this)));
			}
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
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
	 * Delete the group.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		global $pines;
		$descendents = $pines->user_manager->get_group_descendents($this->guid);
		foreach ($descendents as $cur_group) {
			$cur_entity = group::factory($cur_group);
			if (isset($cur_entity->guid)) {
				if ( !$cur_entity->delete() )
					return false;
			}
		}
		if (!parent::delete())
			return false;
		pines_log("Deleted group $this->name [$this->groupname].", 'notice');
		return true;
	}

	/**
	 * Save the group.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->groupname))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the group.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_user', 'form_group', 'content');
		$module->entity = $this;
		$module->display_abilities = gatekeeper("com_user/abilities");
		$module->sections = array('system');
		$module->group_array = $pines->user_manager->get_group_array();
		foreach ($pines->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}
}

?>