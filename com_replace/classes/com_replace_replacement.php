<?php
/**
 * com_replace_replacement class.
 *
 * @package Pines
 * @subpackage com_replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A replacement.
 *
 * @package Pines
 * @subpackage com_replace
 */
class com_replace_replacement extends entity {
	/**
	 * Load a replacement.
	 * @param int $id The ID of the replacement to load, 0 for a new replacement.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_replace', 'replacement');
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
		$this->strings = array();
		$this->conditions = array();
	}

	/**
	 * Create a new instance.
	 * @return com_replace_replacement The new instance.
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
	 * Delete the replacement.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted replacement $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the replacement.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the replacement.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_replace', 'replacement/form', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Determine if this replacement should run.
	 *
	 * This function will check the conditions of the replacement. If the
	 * replacement is disabled or any of the conditions aren't met, it will
	 * return false.
	 *
	 * @return bool True if the replacement is ready, false otherwise.
	 */
	public function ready() {
		if (!$this->enabled)
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