<?php
/**
 * com_sales_cashcount class.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A cash count.
 *
 * @package Components
 * @subpackage sales
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
		if ($id > 0) {
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults
		$this->status = 'pending';
		$this->audits = $this->deposits = $this->skims = $this->count = $this->count_out = array();
		$this->currency_symbol = $pines->config->com_sales->currency_symbol;
		// Create a currency array.
		foreach ($pines->config->com_sales->currency_denominations as $cur_currency) {
			$key = str_replace('.', '_', $cur_currency);
			$this->currency[$key] = $cur_currency;
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
	 * Print a form to cash out a cashcount.
	 * @return module The form's module.
	 */
	public function cash_out() {
		global $pines;
		$module = new module('com_sales', 'cashcount/formcashout', 'content');
		$module->entity = $this;
		return $module;
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
		$this->update_total();
		$module = new module('com_sales', 'cashcount/formreview', 'content');
		$module->entity = $this;
		return $module;
	}

	/**
	 * Save the Cash Count.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		$this->update_total();
		return parent::save();
	}

	/**
	 * Update the Cash Count total to include new sales, skims, and deposits.
	 * @return bool True on success, false on failure.
	 */
	public function update_total() {
		global $pines;
		$this->total = $this->float;
		// Update the total in the drawer for each skim, deposit or sale made.
		if (isset($this->guid)) {
			$new_txs = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_tx),
					array('&',
						'tag' => array('com_sales', 'transaction', 'payment_tx'),
						'gte' => array('p_cdate', (int) $this->p_cdate),
						'ref' => array('group', $this->group)
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
			// Subtract all the skims.
			foreach ($this->skims as $cur_skim) {
				$this->total -= $cur_skim->total;
			}
			// And add all the deposits.
			foreach ($this->deposits as $cur_deposit) {
				$this->total += $cur_deposit->total;
			}
		}
	}
}

?>