<?php
/**
 * com_hrm_application class.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An application that has been submitted.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_application extends entity {
	/**
	 * Load an application.
	 * @param int $id The ID of the application to load, 0 for a new application.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'application');
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
		$this->address_type = 'us';
		$this->education = $this->employment = array();
		$this->references = array(
			array(
				'name' => 'Name',
				'phone' => 'Phone Number',
				'occupation' => 'Occupation'
			),
			array(
				'name' => 'Name',
				'phone' => 'Phone Number',
				'occupation' => 'Occupation'
			),
			array(
				'name' => 'Name',
				'phone' => 'Phone Number',
				'occupation' => 'Occupation'
			)
		);
		$this->status = 'pending';
	}

	/**
	 * Create a new instance.
	 * @return com_hrm_application The new instance.
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
	 * Delete the application.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted employement application $this->name_first $this->name_last.", 'notice');
		return true;
	}

	/**
	 * Print a form to apply for employment.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_hrm', 'application/form', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form to view an employment application.
	 * @return module The form's module.
	 */
	public function view_application() {
		global $pines;
		$module = new module('com_hrm', 'application/view', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>