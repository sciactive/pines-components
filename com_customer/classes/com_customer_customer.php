<?php
/**
 * com_customer_customer class.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A customer.
 *
 * @package Pines
 * @subpackage com_customer
 */
class com_customer_customer extends entity {
	/**
	 * Load a customer.
	 * @param int $id The ID of the customer to load, 0 for a new customer.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_customer', 'customer');
		// Defaults.
		$this->points = 0;
		$this->peak_points = 0;
		$this->total_points = 0;
		$this->address_type = 'us';
		$this->addresses = array();
		$this->attributes = array();
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
	 * Delete the customer.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted customer $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the customer.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Add or subtract points from the customer's profile.
	 *
	 * @param int $point_adjust The positive or negative point value to add.
	 */
	function adjust_points($point_adjust) {
		$point_adjust = (int) $point_adjust;
		// Check that there is a point value.
		if (!is_int($this->points))
			$this->points = 0;
		// Check the total value.
		if (!is_int($this->total_points))
			$this->total_points = $this->points;
		// Check the peak value.
		if (!is_int($this->peak_points))
			$this->peak_points = $this->points;
		// Do the adjustment.
		if ($point_adjust != 0) {
			if ($point_adjust > 0)
				$this->total_points += $point_adjust;
			$this->points += $point_adjust;
			if ($this->points > $this->peak_points)
				$this->peak_points = $this->points;
		}
	}

	/**
	 * Print a form to edit the customer.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->editor->load();
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_customer', 'form_customer', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>