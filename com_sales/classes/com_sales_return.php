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
	 * Attach a sale to the return.
	 *
	 * A return can only have one attached sale, but a sale can be attached to
	 * many returns.
	 *
	 * @param com_sales_sale &$sale The sale to attach.
	 * @return bool True on success, false on failure.
	 */
	public function attach_sale(&$sale) {
		if (isset($this->sale) || $sale->status != 'paid')
			return false;
		$this->sale = $sale;
		$this->customer = $sale->customer;
		$this->products = (array) $sale->products;
		$this->payments = (array) $sale->payments;
		$payment_total = 0;
		foreach ($this->payments as $key => &$cur_payment) {
			// Get rid of non-tendered payments.
			if ($cur_payment['status'] != 'tendered') {
				unset($this->payments[$key]);
				continue;
			}
			/* Is there a change type that's not cash?
			// Put all cash into one payment.
			if ($cur_payment['entity']->change_type) {
				if (isset($change_type_key)) {
					$this->payments[$change_type_key]['amount'] += $cur_payment['amount'];
					unset($this->payments[$key]);
					continue;
				}
				$change_type_key = $key;
			}
			*/
			// If we have enough returned already, we don't need any more.
			if ($payment_total >= $sale->total) {
				unset($this->payments[$key]);
				continue;
			}
			// Reduce the amount to however much is left to return the sale
			// total.
			$cur_payment['amount'] -= ($payment_total + $cur_payment['amount']) - $sale->total;
			$payment_total += $cur_payment['amount'];
			// Return payments are now pending.
			$cur_payment['status'] = 'pending';
		}
		unset($cur_payment);
	}

	/**
	 * Delete the return.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted return $this->id.", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the return.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		if (isset($this->sale) && !$this->sale->guid) {
			pines_notice('The sale associated with this return could not be found.');
			return;
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

	/**
	 * Save the return.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $pines;
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_sales_return');
		return parent::save();
	}
}

?>