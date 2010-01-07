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
 * @todo Move functions from com_customer class to here.
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
			$entity = $config->entity_manager->get_entity($id, $this->tags, get_class($this));
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
	 * Print a form to edit the customer's account.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_customer', 'form_customer', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>