<?php
/**
 * com_sales_manufacturer class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A manufacturer.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_manufacturer extends entity {
	/**
	 * Load a manufacturer.
	 * @param int $id The ID of the manufacturer to load, 0 for a new manufacturer.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'manufacturer');
		if ($id > 0) {
			global $config;
			$entity = $config->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->parent = $entity->parent;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $config;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$config->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the manufacturer.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted manufacturer $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the manufacturer.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the manufacturer.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_sales', 'form_manufacturer', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>