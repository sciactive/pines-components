<?php
/**
 * com_packager_package class.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A package.
 *
 * @package Pines
 * @subpackage com_packager
 */
class com_packager_package extends entity {
	/**
	 * Load a package.
	 * @param int $id The ID of the package to load, 0 for a new package.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_packager', 'package');
		// Defaults.
		$this->type = 'component';
		$this->attributes = $this->meta = array();
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
	 * Delete the package.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted package $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the package.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the package.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->editor->load();
		$pines->com_pgrid->load();
		$module = new module('com_packager', 'form_package', 'content');
		$module->entity = $this;
		$module->components = $pines->all_components;

		return $module;
	}
}

?>