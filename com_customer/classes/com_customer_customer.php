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
 * A com_sales customer, with extended functionality.
 *
 * @package Pines
 * @subpackage com_customer
 */
class com_customer_customer extends com_sales_customer {
	/**
	 * Load a customer.
	 * @param int $id The ID of the customer to load, null for a new customer.
	 */
	public function __construct($id = null) {
		parent::__construct();
		$this->add_tag('com_sales', 'customer');
		if (!is_null($id)) {
			global $config;
			$entity = $config->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->parent = $entity->parent;
			$this->tags = $entity->tags;
			$this->entity_cache = array();
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
	 * Add or subtract points from the customer's account.
	 *
	 * @param int $point_adjust The positive or negative point value to add.
	 */
	function adjust_points($point_adjust) {
		if (!isset($this->com_customer))
			$this->com_customer = (object) array();
		$point_adjust = (int) $point_adjust;
		// Check that there is a point value.
		if (!is_int($this->com_customer->points))
			$this->com_customer->points = 0;
		// Check the total value.
		if (!is_int($this->com_customer->total_points))
			$this->com_customer->total_points = $this->com_customer->points;
		// Check the peak value.
		if (!is_int($this->com_customer->peak_points))
			$this->com_customer->peak_points = $this->com_customer->points;
		// Do the adjustment.
		if ($point_adjust != 0) {
			if ($point_adjust > 0)
				$this->com_customer->total_points += $point_adjust;
			$this->com_customer->points += $point_adjust;
			if ($this->com_customer->points > $this->com_customer->peak_points)
				$this->com_customer->peak_points = $this->com_customer->points;
		}
	}

	/**
	 * Print a form to edit the customer's account.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $config;
		$config->editor->load();
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_customer', 'form_customer', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>