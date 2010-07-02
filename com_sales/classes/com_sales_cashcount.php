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
 * @package Pines
 * @subpackage com_sales
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
	 * @return com_sales_cashcount The new instance.
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
		$module = new module('com_sales', 'cashcount/form', 'content');
		$module->entity = $this;
		return $module;
	}

	/**
	 * Print a form to review the cash count.
	 * @return module The form's module.
	 */
	public function print_review() {
		global $pines;
		$module = new module('com_sales', 'cashcount/formreview', 'content');
		$module->entity = $this;
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
			$new_txs = $pines->entity_manager->get_entities(
					array('class' => com_sales_tx),
					array('&',
						'gte' => array('p_cdate', (int) $this->p_cdate),
						'ref' => array('group', $this->group),
						'tag' => array('com_sales', 'transaction', 'payment_tx')
					)
				);
			// Look for all transactions that resulted in cash being tendered.
			foreach ($new_txs as $cur_tx) {
				if (!$cur_tx->ref->change_type)
					continue;
				if ($cur_tx->type == 'payment_received') {
					$this->total += $cur_tx->amount;
				} elseif ($cur_tx->type == 'change_given' || $cur_tx->type == 'payment_voided' || $cur_tx->type == 'payment_returned') {
					$this->total -= $cur_tx->amount;
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