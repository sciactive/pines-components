<?php
/**
 * com_customertimer_floor class.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
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
		$this->layout = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
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
	 * Delete the floor.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted floor $this->name.", 'notice');
		return true;
	}

	public function get_background() {
		global $pines;
		return "{$pines->config->rela_location}{$pines->config->setting_upload}floorplans/{$this->background}";
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
		global $pines;
		$module = new module('com_customertimer', 'form_floor', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>