<?php
/**
 * com_customertimer_floor class.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A floorplan of stations.
 *
 * @package Pines
 * @subpackage com_customertimer
 */
class com_customertimer_floor extends entity {
	/**
	 * Load a floor.
	 * @param int $id The ID of the floor to load, 0 for a new floor.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_customertimer', 'floor');
		// Defaults.
		$this->enabled = true;
		$this->stations = array();
		$this->active_stations = array();
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
	 * @return com_customertimer_floor The new instance.
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
	 * Delete the floor.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted floor $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the floor.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the floor.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_customertimer', 'form_floor', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form to time customers on the floor.
	 * @return module The form's module.
	 */
	public function print_timer() {
		global $pines;
		$module = new module('com_customertimer', 'form_floor_timer', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>