<?php
/**
 * com_hrm_employee class.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A employee.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_employee extends entity {
	/**
	 * Load an employee.
	 * @param int $id The ID of the employee to load, 0 for a new employee.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'employee');
		// Defaults.
		$this->address_type = 'us';
		$this->addresses = array();
		$this->attributes = array();
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
	 * Delete the employee.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted employee $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the employee.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the employee.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $config;
		$config->editor->load();
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_hrm', 'form_employee', 'content');
		$module->entity = $this;

		return $module;
	}

	public function validate() {
		return array(
			'name' => array(
				'required' => 'Please specify a name.'
			),
			'email' => array(
				'required' => 'Please specify an email.',
				'email' => 'The email provided is not valid.'
			),
			'phone_cell' => array(
				'one_required' => array('phones', 'Please specify at least one phone number.'),
				'phone' => 'The phone number provided is not valid.'
			),
			'phone_work' => array(
				'one_required' => array('phones', 'Please specify at least one phone number.'),
				'phone' => 'The phone number provided is not valid.'
			),
			'phone_home' => array(
				'one_required' => array('phones', 'Please specify at least one phone number.'),
				'phone' => 'The phone number provided is not valid.'
			)
		);
	}
}

?>