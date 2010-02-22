<?php
/**
 * com_customer_company class.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A company.
 *
 * @package Pines
 * @subpackage com_customer
 */
class com_customer_company extends entity {
	/**
	 * Load a customer.
	 * @param int $id The ID of the customer to load, 0 for a new customer.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_customer', 'company');
		// Defaults.
		$this->address_type = 'us';
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
	 * Delete the company.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted company $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the company.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the company.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->editor->load();
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_customer', 'form_company', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>