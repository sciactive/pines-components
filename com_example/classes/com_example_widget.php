<?php
/**
 * com_example_widget class.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A widget.
 *
 * @package Pines
 * @subpackage com_example
 */
class com_example_widget extends entity {
	/**
	 * Load a widget.
	 * @param int $id The ID of the widget to load, 0 for a new widget.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_example', 'widget');
		// Defaults.
		$this->enabled = true;
		$this->attributes = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (!isset($entity))
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
	 * Delete the widget.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted widget $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the widget.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the widget.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_example', 'form_widget', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>