<?php
/**
 * com_sales_return class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A return.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_return extends entity {
	/**
	 * Load a return.
	 * @param int $id The ID of the return to load, 0 for a new return.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'return');
		// Defaults.
		$this->products = array();
		$this->payments = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 * @return com_sales_return The new instance.
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
	 * Delete the return.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted return $this->name.", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the return.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		if (isset($this->sale)) {
			if (!$this->sale->guid) {
				pines_notice('The sale associated with this return could not be found.');
				return;
			}

		}
		$module = new module('com_sales', 'return/form', 'content');
		$module->entity = $this;
		$module->categories = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_category),
				array('&',
					'data' => array('enabled', true),
					'tag' => array('com_sales', 'category')
				)
			);
		$module->tax_fees = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_tax_fee),
				array('&',
					'data' => array('enabled', true),
					'tag' => array('com_sales', 'tax_fee')
				)
			);
		$module->payment_types = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_payment_type),
				array('&',
					'data' => array('enabled', true),
					'tag' => array('com_sales', 'payment_type')
				)
			);

		return $module;
	}
}

?>