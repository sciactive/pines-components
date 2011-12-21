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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A return.
 *
 * @package Pines
 * @subpackage com_sales
 * @todo Returning specials.
 */
class com_sales_return extends entity {
	/**
	 * Load a return.
	 * @param int $id The ID of the return to load, 0 for a new return.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'return');
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
		$this->products = array();
		$this->payments = array();
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
	 * Add negative commission to the employee(s).
	 */
	public function add_commission() {
		global $pines;
		if ($this->added_commission || !$pines->config->com_sales->com_hrm)
			return;
		$this->added_commission = true;
		// Go through each product, adding commission.
		foreach ($this->products as &$cur_product) {
			if (!isset($cur_product['salesperson']))
				continue;
			if (!$cur_product['entity']->commissions)
				continue;
			$cur_product['commission'] = 0;
			foreach ($cur_product['entity']->commissions as $cur_commission) {
				if (!$cur_product['salesperson']->in_group($cur_commission['group']))
					continue;
				// Calculate commission.
				switch ($cur_commission['type']) {
					case 'spiff':
						$cur_product['commission'] += (float) $cur_commission['amount'];
						break;
					case 'percent_price':
						$cur_product['commission'] += $this->discount_price($cur_product['price'], $cur_product['discount']) * ( ((float) $cur_commission['amount']) / 100 );
						break;
				}
			}
			if ($cur_product['commission'] == 0)
				continue;
			// Add the negative commission to the user.
			if ((array) $cur_product['salesperson']->commissions !== $cur_product['salesperson']->commissions)
				$cur_product['salesperson']->commissions = array();
			$cur_product['salesperson']->commissions[] = array(
				'date' => time(),
				'amount' => ($cur_product['commission'] * $cur_product['quantity']) * -1,
				'ticket' => $this,
				'product' => $cur_product['entity']
			);
			$cur_product['salesperson']->save();
		}
		unset($cur_product);
		$this->save();
	}

	/**
	 * Approve each payment.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function approve_payments() {
		global $pines;
		$return = true;
		// Go through each payment, and process its approval.
		foreach ($this->payments as &$cur_payment) {
			// If its already approved or tendered, skip it.
			if (in_array($cur_payment['status'], array('approved', 'declined', 'tendered')))
				continue;
			// Check minimum and maximum values.
			if ((float) $cur_payment['amount'] < $cur_payment['entity']->minimum && !$cur_payment['entity']->allow_return) {
				pines_notice("The payment type [{$cur_payment['entity']->name}] requires a minimum payment of {$cur_payment['entity']->minimum}.");
				$return = false;
				continue;
			}
			if (isset($cur_payment['entity']->maximum) && (float) $cur_payment['amount'] > $cur_payment['entity']->maximum) {
				pines_notice("The payment type [{$cur_payment['entity']->name}] requires a maximum payment of {$cur_payment['entity']->maximum}.");
				$return = false;
				continue;
			}
			// Check if return fee payment is allowed.
			if ((float) $cur_payment['amount'] < 0 && !$cur_payment['entity']->allow_return) {
				pines_notice("The payment type [{$cur_payment['entity']->name}] cannot be charged on a return. (It can't have a negative amount.)");
				$return = false;
				continue;
			}
			// If the amount is negative, we are charging money.
			$type = $cur_payment['amount'] < 0 ? 'charge' : 'return';
			// If we're charging, the amount needs to be turned positive.
			if ($type == 'charge')
				$cur_payment['amount'] = $cur_payment['amount'] * -1;
			// Call the payment processing for approval.
			$pines->com_sales->call_payment_process(array(
				'action' => 'approve',
				'type' => $type,
				'name' => $cur_payment['entity']->processing_type,
				'payment' => &$cur_payment,
				'ticket' => &$this
			));
			// Now turn it back negative.
			if ($type == 'charge')
				$cur_payment['amount'] = $cur_payment['amount'] * -1;
			if (!in_array($cur_payment['status'], array('approved', 'declined')))
				$return = false;
		}
		unset($cur_payment);
		return $return;
	}

	/**
	 * Attach a sale to the return.
	 *
	 * A return can only have one attached sale, but a sale can be attached to
	 * multiple returns.
	 *
	 * @param com_sales_sale &$sale The sale to attach.
	 * @return bool True on success, false on failure.
	 */
	public function attach_sale(&$sale) {
		if (isset($this->sale) || $sale->status != 'paid')
			return false;
		if (!$sale->save()) {
			// If the sale can't be modified, processing will fail.
			pines_error('The sale could not be modified. Do you have permission?');
			return false;
		}
		$this->sale = $sale;
		$this->products = $this->get_sale_products();
		if (!$this->products) {
			pines_notice('All products from that sale have been returned already.');
			unset($this->sale);
			return false;
		}
		$this->specials = (array) $sale->specials;
		$this->payments = (array) $sale->payments;
		$payment_total = 0;
		//$sale->returned_total is updated when return is processed.
		//$sale_total = $sale->total - (float) $sale->returned_total;
		$this->total();
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
			if ($payment_total >= $this->total) {
				unset($this->payments[$key]);
				continue;
			}
			// Reduce the amount to however much is left to return the sale
			// total.
			if ($cur_payment['entity']->allow_return && (($payment_total + $cur_payment['amount']) - $this->total > 0))
				$cur_payment['amount'] -= ($payment_total + $cur_payment['amount']) - $this->total;
			$payment_total += $cur_payment['amount'];
			// Return payments are now pending.
			$cur_payment['status'] = 'pending';
		}
		unset($cur_payment);
		$this->customer = $sale->customer;
		return true;
	}

	/**
	 * Complete the return.
	 *
	 * This process receives stock and creates payment transaction entries for
	 * each payment.
	 *
	 * A sale transaction is created, and the return's status is changed to
	 * 'processed'.
	 *
	 * @return bool True on success, false on any failure.
	 */
	public function complete() {
		global $pines;
		if ($this->status == 'processed' || $this->status == 'voided')
			return true;
		if (empty($this->products)) {
			pines_notice('Return has no products');
			return false;
		}
		if (isset($this->sale) && !$this->sale->save()) {
			// If the sale can't be modified, processing will fail.
			pines_error('The sale could not be modified. Do you have permission?');
			return false;
		}
		// Keep track of the whole process.
		$return = true;
		// These will be searched through to match products to stock entries.
		if ($pines->config->com_sales->com_customer && !isset($this->customer)) {
			foreach ($this->products as &$cur_product) {
				if (!$cur_product['entity'] || ($cur_product['entity']->require_customer)) {
					pines_notice('One of the products on this return requires a customer. Please select a customer for this return before processing.');
					return false;
				}
			}
			unset($cur_product);
		}
		// Calculate and save the return's totals.
		if (!$this->total()) {
			pines_notice('Couldn\'t total return.');
			return false;
		}
		if (empty($this->payments) && $this->total > 0) {
			pines_notice('Return has no payments');
			return false;
		}
		// Go through each product, and find/create corresponding stock entries.
		foreach ($this->products as &$cur_product) {
			if ($cur_product['entity']->stock_type == 'non_stocked') {
				$cur_product['stock_entities'] = array();
			} else {
				if (isset($this->sale)) {
					$cur_product['stock_entities'] = array_slice((array) $cur_product['stock_entities'], 0, (int) $cur_product['quantity']);
				} else {
					for ($i = 0; $i < $cur_product['quantity']; $i++) {
						$stock = com_sales_stock::factory();
						$stock->product = $cur_product['entity'];
						if ($cur_product['entity']->serialized)
							$stock->serial = $cur_product['serial'];
						$cur_product['stock_entities'][] = $stock;
						unset($stock);
					}
				}
			}
		}
		unset($cur_product);

		if (!$this->approve_payments()) {
			pines_notice('The return cannot be completed until all payments have been approved or declined.');
			return false;
		}
		if (!$this->tender_payments()) {
			pines_notice('All payments have not been tendered. The return was not completed. Please check the status on each payment.');
			return false;
		}
		if (!isset($this->amount_due) || $this->amount_due > 0) {
			pines_notice('The return cannot be completed while there is still an amount remaining.');
			return false;
		}

		// Receive stock.
		$stock_result = $this->receive_stock();
		$return = $return && $stock_result;
		if (!$stock_result)
			pines_notice('Not all stock could be received into inventory while processing. Please check that all stock was correctly entered.');
		$this->perform_actions();
		if (!$this->added_commission) {
			// Add commission.
			$this->add_commission();
		}

		if (isset($this->sale)) {
			// Go through each product, and mark the sold quantity in the sale.
			foreach ($this->products as &$cur_product) {
				if (!isset($this->sale->products[$cur_product['sale_key']]['returned_quantity']))
					$this->sale->products[$cur_product['sale_key']]['returned_quantity'] = 0;
				$this->sale->products[$cur_product['sale_key']]['returned_quantity'] += $cur_product['quantity'];
			}
			unset($cur_product);
			if ($this->sale->warehouse) {
				$this->sale->warehouse = false;
				foreach ($this->sale->products as &$cur_product) {
					// If the product's warehouse item count is greater than the returned quantity, it still needs to be fulfilled.
					if ($cur_product['delivery'] == 'warehouse' && $cur_product['returned_quantity'] < ($cur_product['quantity'] - (count($cur_product['stock_entities']) - count((array) $cur_product['returned_stock_entities'])))) {
						$this->sale->warehouse = true;
						break;
					}
				}
				unset($cur_product);
			}
			$return = $this->sale->save() && $return;
		}

		// Complete the transaction.
		if ($return) {
			// Make a transaction entry.
			$tx = com_sales_tx::factory('sale_tx');

			$this->status = 'processed';
			$tx->type = 'returned';

			// Make sure we have a GUID before saving the tx.
			if (!isset($this->guid))
				$return = $this->save() && $return;

			$tx->ticket = $this;
			$return = $tx->save() && $return;
		}

		$this->process_date = time();

		return ($this->save() && $return);
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
	 * Calculate the discount price based on a flat or percent discount.
	 * @param float $price The original price.
	 * @param string $discount The discount to give. Flat discount begins with $, percent ends with %.
	 * @return float The discounted price.
	 */
	public function discount_price($price, $discount) {
		$discount_price = (float) $price;
		if (preg_match('/^\$-?\d+(\.\d+)?$/', $discount)) {
			// This is an exact discount.
			$discount = (float) preg_replace('/[^0-9.-]/', '', $discount);
			$discount_price = $price - $discount;
		} elseif (preg_match('/^-?\d+(\.\d+)?%$/', $discount)) {
			// This is a percentage discount.
			$discount = (float) preg_replace('/[^0-9.-]/', '', $discount);
			$discount_price = $price - ($price * ($discount / 100));
		}
		return $discount_price;
	}

	/**
	 * Get a product array from an attached sale.
	 *
	 * Ignores products that have already been returned.
	 *
	 * @return array The product array.
	 */
	public function get_sale_products() {
		$products = (array) $this->sale->products;
		foreach ($products as $key => &$cur_product) {
			// Add the original sale key to all product entries.
			$cur_product['sale_key'] = $key;
			// Is it already returned?
			$cur_product['quantity'] -= (int) $cur_product['returned_quantity'];
			if ($cur_product['quantity'] <= 0) {
				unset($products[$key]);
				continue;
			}
			// Remove products that have already been returned.
			if (is_array($cur_product['stock_entities'])) {
				foreach ($cur_product['stock_entities'] as $stock_key => &$cur_stock) {
					// TODO: Update $cur_product['returned_stock_entities'] when return is processed.
					if ($cur_stock->in_array($cur_product['returned_stock_entities']))
						unset($cur_product['stock_entities'][$stock_key]);
				}
				unset($cur_stock);
			}
		}
		unset($cur_product);
		return $products;
	}

	/**
	 * Run the product actions associated with the products on this return.
	 */
	public function perform_actions() {
		global $pines;
		if ($this->performed_actions)
			return;
		$this->performed_actions = true;
		// Go through each product, calling actions.
		foreach ($this->products as &$cur_product) {
			// Call product actions for all products without stock entries.
			$i = $cur_product['quantity'] - count($cur_product['stock_entities']);
			if ($i > 0) {
				$pines->com_sales->call_product_actions(array(
					'type' => 'returned',
					'product' => $cur_product['entity'],
					'ticket' => $this,
					'serial' => $cur_product['serial'],
					'price' => $cur_product['price'],
					'discount' => $cur_product['discount'],
					'line_total' => $cur_product['line_total'],
					'fees' => $cur_product['fees']
				), $i);
			}
			// Call product actions on stock.
			if (!is_array($cur_product['stock_entities']))
				continue;
			foreach ($cur_product['stock_entities'] as &$cur_stock) {
				$pines->com_sales->call_product_actions(array(
					'type' => 'returned',
					'product' => $cur_product['entity'],
					'stock_entry' => $cur_stock,
					'ticket' => $this,
					'serial' => $cur_product['serial'],
					'price' => $cur_product['price'],
					'discount' => $cur_product['discount'],
					'line_total' => $cur_product['line_total'],
					'fees' => $cur_product['fees']
				));
			}
		}
		unset($cur_product);
		$this->save();
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
					'tag' => array('com_sales', 'category'),
					'data' => array('enabled', true)
				)
			);
		$module->tax_fees = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_tax_fee),
				array('&',
					'tag' => array('com_sales', 'tax_fee'),
					'data' => array('enabled', true)
				)
			);
		$module->payment_types = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_payment_type),
				array('&',
					'tag' => array('com_sales', 'payment_type'),
					'data' => array('enabled', true)
				)
			);

		return $module;
	}

	/**
	 * Print a receipt of the return.
	 * @return module The form's module.
	 */
	function print_receipt() {
		$module = new module('com_sales', 'sale/receipt', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Receive the stock on this return into current inventory.
	 * @return bool True on success, false on any failure.
	 */
	public function receive_stock() {
		if ($this->received_stock)
			return false;
		if (isset($this->sale) && !$this->sale->save()) {
			// If the sale can't be modified, processing will fail.
			pines_error('The sale could not be modified. Do you have permission?');
			return false;
		}
		$this->received_stock = true;
		// Keep track of the whole process.
		$return = true;
		// Go through each product, marking its stock as returned.
		foreach ($this->products as &$cur_product) {
			// Receive stock into inventory.
			if (!is_array($cur_product['stock_entities']))
				continue;
			foreach ($cur_product['stock_entities'] as &$cur_stock) {
				if (!isset($cur_stock->guid))
					$cur_stock->save();
				// Receive the stock into inventory.
				if (!$cur_stock->receive('sale_returned', $this, null, false)) {
					$return = false;
					continue;
				}
				if (!$cur_stock->save()) {
					$return = false;
					continue;
				}
				if (isset($this->sale))
					$this->sale->products[$cur_product['sale_key']]['returned_stock_entities'][] = $cur_stock;
			}
		}
		unset($cur_product);
		if (isset($this->sale))
			$return = $this->sale->save() && $return;
		return ($this->save() && $return);
	}

	/**
	 * Print a form to swap salespeople.
	 *
	 * Uses a page override to only print the form.
	 *
	 * @return module The form's module.
	 */
	public function salesrep_form() {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_sales', 'forms/salesrep', 'content');
		$module->entity = $this;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Save the return.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $pines;
		if (!isset($this->status))
			$this->status = 'quoted';
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_sales_return');
		return parent::save();
	}

	/**
	 * Swap a salesperson on an item in the return.
	 *
	 * @param int $key The key (index) of the product.
	 * @param user $new_salesrep The new salesperson for the item.
	 * @return bool True on success, false on failure.
	 */
	public function swap_salesrep($key, $new_salesrep = null) {
		global $pines;
		// Make sure this return has been processed.
		if ($this->status != 'processed') {
			pines_notice('This return isn\'t complete, items cannot be swapped.');
			return false;
		}

		if (!isset($this->products[$key])) {
			pines_notice('This item cannot be swapped, because it is not on the return.');
			return false;
		}

		$old_salesrep = $this->products[$key]['salesperson'];
		if (isset($old_salesrep)) {
			foreach ($old_salesrep->commissions as &$cur_commission) {
				if ($this->is($cur_commission['ticket']) && $this->products[$key]['entity']->is($cur_commission['product'])) {
					$cur_commission['note'] .= " Credited/Swapped to {$new_salesrep->name} [{$new_salesrep->username}].";
					break;
				}
			}
			// Add a positive amount to offset the negative from the return.
			$old_salesrep->commissions[] = array(
				'date' => time(),
				'amount' => $this->products[$key]['commission'] * $this->products[$key]['quantity'],
				'ticket' => $this,
				'product' => $this->products[$key]['entity'],
				'note' => "Credited/Swapped to {$new_salesrep->name} [{$new_salesrep->username}]. This entry offsets the original."
			);
			unset($cur_commission);

			$old_salesrep->save();
		}

		$this->products[$key]['salesperson'] = $new_salesrep;
		$this->products[$key]['commission'] = 0;
		if (!$this->products[$key]['entity']->commissions)
			return true;
		foreach ($this->products[$key]['entity']->commissions as $cur_commission) {
			if (!$new_salesrep->in_group($cur_commission['group']))
				continue;
			// Calculate commission.
			switch ($cur_commission['type']) {
				case 'spiff':
					$this->products[$key]['commission'] += (float) $cur_commission['amount'];
					break;
				case 'percent_price':
					$this->products[$key]['commission'] += $this->discount_price($this->products[$key]['price'], $this->products[$key]['discount']) * ( ((float) $cur_commission['amount']) / 100 );
					break;
			}
		}
		if ($this->products[$key]['commission'] == 0)
			return true;
		// Add the commission to the user.
		if ((array) $new_salesrep->commissions !== $new_salesrep->commissions)
			$new_salesrep->commissions = array();
		$new_salesrep->commissions[] = array(
			'date' => time(),
			'amount' => $this->products[$key]['commission'] * $this->products[$key]['quantity'] * -1,
			'ticket' => $this,
			'product' => $this->products[$key]['entity'],
			'note' => "Credited/Swapped from {$old_salesrep->name} [{$old_salesrep->username}]."
		);

		return $new_salesrep->save();
	}

	/**
	 * Process each payment.
	 *
	 * This process updates "amount_tendered" and "amount_due" on the return
	 * itself.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function tender_payments() {
		global $pines;
		if (isset($this->sale) && !$this->sale->save()) {
			// If the sale can't be modified, processing will fail.
			pines_error('The sale could not be modified. Do you have permission?');
			return false;
		}
		$this->tendered_payments = true;
		if (!is_array($this->payments))
			$this->payments = array();
		if (!is_numeric($this->total))
			return false;
		$total = (float) $this->total;
		$amount_tendered = (float) $this->amount_tendered;
		$amount_due = 0.00;
		$return = true;
		foreach ($this->payments as &$cur_payment) {
			// If it's already tendered, skip it.
			if (in_array($cur_payment['status'], array('declined', 'tendered')))
				continue;
			// If it's not approved, return can't be tendered.
			if ($cur_payment['status'] != 'approved') {
				$return = false;
				continue;
			}
			// If the amount is negative, we are charging money.
			$action = $cur_payment['amount'] < 0 ? 'tender' : 'return';
			// If we're charging, the amount needs to be turned positive.
			if ($action == 'tender')
				$cur_payment['amount'] = $cur_payment['amount'] * -1;
			// Call the payment processing.
			$pines->com_sales->call_payment_process(array(
				'action' => $action,
				'name' => $cur_payment['entity']->processing_type,
				'payment' => &$cur_payment,
				'ticket' => &$this
			));
			// Now turn it back negative.
			if ($action == 'tender')
				$cur_payment['amount'] = $cur_payment['amount'] * -1;
			// If the payment went through, record it, if it didn't and it
			// wasn't declined, consider it a failure.
			if ($cur_payment['status'] == 'tendered') {
				// Add to the amount tendered.
				$amount_tendered += (float) $cur_payment['amount'];
				// And if there is a sale, add it to the sale's returned value.
				if (isset($this->sale))
					$this->sale->returned_total += (float) $cur_payment['amount'];
				// Make a transaction entry.
				$tx = com_sales_tx::factory('payment_tx');
				if ($action == 'tender') {
					$tx->type = 'payment_received';
					$tx->amount = (float) $cur_payment['amount'] * -1;
				} else {
					$tx->type = 'payment_returned';
					$tx->amount = (float) $cur_payment['amount'];
				}
				$tx->ref = $cur_payment['entity'];

				// Make sure we have a GUID before saving the tx.
				if (!isset($this->guid))
					$return = $this->save() && $return;

				$tx->ticket = $this;
				$return = $tx->save() && $return;
			} else {
				if ($cur_payment['status'] != 'declined')
					$return = false;
			}
		}
		unset($cur_payment);
		$amount_due = $total - $amount_tendered;
		if ($amount_due < 0.00)
			$amount_due = 0.00;
		$this->amount_tendered = (float) $pines->com_sales->round($amount_tendered);
		$this->amount_due = (float) $pines->com_sales->round($amount_due);
		if (isset($this->sale))
			$return = $this->sale->save() && $return;
		return ($this->save() && $return);
	}

	/**
	 * Calculate and set the return's totals.
	 *
	 * This process adds "line_total" and "fees" to each product on the return,
	 * and adds "subtotal", "item_fees", "taxes", and "total" to the return
	 * itself.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function total() {
		global $pines;
		if (!is_array($this->products) || in_array($this->status, array('processed', 'voided')))
			return false;
		// We need a list of enabled taxes and fees.
		$tax_fees = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_tax_fee),
				array('&',
					'tag' => array('com_sales', 'tax_fee'),
					'data' => array('enabled', true)
				)
			);
		foreach ($tax_fees as $key => $cur_tax_fee) {
			foreach($cur_tax_fee->locations as $cur_location) {
				// If we're in one of its groups, don't remove it.
				if ($_SESSION['user']->in_group($cur_location))
					continue 2;
			}
			// We're not in any of its groups, so remove it.
			unset($tax_fees[$key]);
		}
		$subtotal = 0.00;
		$taxes = 0.00;
		$item_fees = 0.00;
		$total = 0.00;
		// How many times to apply a flat tax.
		$tax_qty = 0;
		$taxable_subtotal = 0.00;
		$return_fees = 0.00;
		// Go through each product, calculating its line total and fees.
		foreach ($this->products as &$cur_product) {
			$price = (float) $cur_product['price'];
			$qty = (int) $cur_product['quantity'];
			$discount = $cur_product['discount'];
			$cur_return_fee = 0.00;
			// Return fee needs to be calculated before discount.
			foreach ((array) $cur_product['entity']->return_checklists as $cur_checklist) {
				if (!$cur_checklist->enabled)
					continue;
				foreach ($cur_checklist->conditions as $cur_condition) {
					// Check if this should be added.
					if ($cur_condition['always'] || $cur_product['return_checklists']["G{$cur_checklist->guid}"]["C{$cur_condition['condition']}"]) {
						// Calculate return fee.
						switch ($cur_condition['type']) {
							case 'flat_rate':
							default:
								$cur_return_fee += (float) $pines->com_sales->round($cur_condition['amount'] * $qty);
								break;
							case 'percentage':
								$cur_return_fee += (float) $pines->com_sales->round($pines->com_sales->round($price * ($cur_condition['amount'] / 100)) * $qty);
								break;
						}
					}
				}
			}
			if ($cur_product['entity']->discountable && $discount != "") {
				$discount_price = $this->discount_price($price, $discount);
				// Check that the discount doesn't lower the item's price below the floor.
				if ($cur_product['entity']->floor && $pines->com_sales->round($discount_price) < $pines->com_sales->round($cur_product['entity']->floor)) {
					pines_notice("The discount on {$cur_product['entity']->name} lowers the product's price below the limit. The discount was removed.");
					$discount = $cur_product['discount'] = '';
				} else {
					$price = $discount_price;
				}
			}
			// Check that the price is above the floor of the product.
			if ($cur_product['entity']->floor && $price < $cur_product['entity']->floor) {
				pines_notice("The product {$cur_product['entity']->name} cannot be priced lower than {$cur_product['entity']->floor}.");
				return false;
			}
			// Check that the price is below the ceiling of the product.
			if ($cur_product['entity']->ceiling && $price > $cur_product['entity']->ceiling) {
				pines_notice("The product {$cur_product['entity']->name} cannot be priced higher than {$cur_product['entity']->ceiling}.");
				return false;
			}
			$line_total = $price * $qty;
			$cur_item_fees = 0.00;
			if (!$cur_product['entity']->tax_exempt) {
				$taxable_subtotal += (float) $pines->com_sales->round($line_total);
				$tax_qty += $qty;
			}
			if (is_array($cur_product['entity']->additional_tax_fees)) {
				// Add item fees.
				foreach ($cur_product['entity']->additional_tax_fees as $cur_tax_fee) {
					if ($cur_tax_fee->type == 'percentage') {
						$cur_item_fees += ($cur_tax_fee->rate / 100) * $line_total;
					} elseif ($cur_tax_fee->type == 'flat_rate') {
						$cur_item_fees += $cur_tax_fee->rate * $qty;
					}
				}
			}
			$cur_product['line_total'] = (float) $pines->com_sales->round($line_total);
			$cur_product['fees'] = (float) $pines->com_sales->round($cur_item_fees);
			$cur_product['return_fee'] = (float) $pines->com_sales->round($cur_return_fee);
			$item_fees += $cur_product['fees'];
			$subtotal += $cur_product['line_total'];
			$return_fees += $cur_product['return_fee'];
		}
		unset($cur_product);
		$this->subtotal = (float) $pines->com_sales->round($subtotal);

		// Now that we know the subtotal, we can use it for specials.
		$total_before_tax_specials = 0.00;
		$total_specials = 0.00;
		foreach ((array) $this->specials as $cur_special) {
			if ($cur_special['entity']->before_tax)
				$total_before_tax_specials += $cur_special['discount'];
			$total_specials += $cur_special['discount'];
		}
		// Add location taxes.
		foreach ($tax_fees as $cur_tax_fee) {
			if ($cur_tax_fee->type == 'percentage')
				$taxes += ($cur_tax_fee->rate / 100) * ($taxable_subtotal - $total_before_tax_specials);
			elseif ($cur_tax_fee->type == 'flat_rate')
				$taxes += $cur_tax_fee->rate * $tax_qty;
		}
		$this->total_specials = (float) $pines->com_sales->round($total_specials);
		$this->item_fees = (float) $pines->com_sales->round($item_fees);
		$this->taxes = (float) $pines->com_sales->round($taxes);
		$this->return_fees = (float) $pines->com_sales->round($return_fees);
		// The total can now be calculated.
		$total = ($this->subtotal - $this->total_specials) + $this->item_fees + $this->taxes - $this->return_fees;
		$this->total = (float) $total;
		return true;
	}
}

?>