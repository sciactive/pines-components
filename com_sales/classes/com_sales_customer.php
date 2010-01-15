<?php
/**
 * com_sales_customer class.
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
 * A customer.
 *
 * This class is temporary and is only used for transitioning customers from
 * com_sales to com_customer.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_customer extends com_customer_customer {
	/**
	 * Load a customer.
	 * @param int $id The ID of the customer to load, null for a new customer.
	 */
	public function __construct($id = null) {
		parent::__construct();
		$this->add_tag('com_customer', 'customer');
		if (!is_null($id)) {
			global $config;
			$entity = $config->entity_manager->get_entity(array('guid' => $id, 'class' => get_class($this)));
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
}

?>