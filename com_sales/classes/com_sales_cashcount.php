<?php
/**
 * com_sales_cashcount class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A cash count.
 *
 * @package com_sales
 */
class com_sales_cashcount extends entity {
	/**
	 * Load a cash count.
	 * @param int $id The ID of the cashcount to load, 0 for a new cashcount.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		global $pines;
		$this->add_tag('com_sales', 'cashcount');
		// Defaults
		$this->status = 'pending';
		$this->audits = $this->deposits = $this->skims = $this->count = array();
		$this->currency_symbol = $pines->config->com_sales->currency_symbol;
		$this->currency = $pines->config->com_sales->currency_denominations;
		if ($id > 0) {
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
	 * Delete the cash count.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		// Delete all audits and skims for this cash count as well.
		foreach ($this->audits as &$cur_audit)
			$cur_audit->delete();
		foreach ($this->skims as &$cur_skim)
			$cur_skim->delete();
		foreach ($this->deposits as &$cur_deposit)
			$cur_deposit->delete();

		if (!parent::delete())
			return false;
		pines_log("Deleted Cash Count {$this->guid}.", 'notice');
		return true;
	}


	/**
	 * Print a form to edit the cash count.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_sales', 'form_cashcount', 'content');
		$module->entity = $this;
		return $module;
	}

	/**
	 * Print a form to review the cash count.
	 * @return module The form's module.
	 */
	public function print_review() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_sales', 'form_cashcount_review', 'content');
		$module->entity = $this;
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/form_cashcount_review'];
		return $module;
	}

	/**
	 * Update the Cash Count total to include new sales, and skims.
	 * @return bool True on success, false on failure.
	 */
	public function update_total() {
		global $pines;
		$this->total = $this->float;
		// Update the total in the drawer for each skim, deposit or sale made.
		if (isset($this->guid)) {
			$new_sales = $pines->entity_manager->get_entities(array('gte' => array('p_cdate' => (int) $this->p_cdate), 'ref' => array('group' => $this->group), 'tags' => array('com_sales', 'sale'), 'class' => com_sales_sale));
			// Look for all sales that resulted in cash being tendered.
			foreach ($new_sales as $cur_sale) {
				foreach ($cur_sale->payments as $cur_payment) {
					if ($cur_payment['entity']->change_type && $cur_payment['status'] == 'tendered')
						$this->total += $cur_payment['amount'];
				}
			}
			foreach ($this->skims as $cur_skim)
				$this->total -= $cur_skim->variance;
		}
		
	}

	/**
	 * Save the Cash Count.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		$this->update_total();
		return parent::save();
	}
}

?>