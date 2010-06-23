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
			if ((float) $cur_payment['amount'] < $cur_payment['entity']->minimum) {
				pines_notice("The payment type [{$cur_payment['entity']->name}] requires a minimum payment of {$cur_payment['entity']->minimum}.");
				$return = false;
				continue;
			}
			if (isset($cur_payment['entity']->maximum) && (float) $cur_payment['amount'] > $cur_payment['entity']->maximum) {
				pines_notice("The payment type [{$cur_payment['entity']->name}] requires a maximum payment of {$cur_payment['entity']->maximum}.");
				$return = false;
				continue;
			}
			// Call the payment processing for approval.
			$pines->com_sales->call_payment_process(array(
				'action' => 'approve',
				'name' => $cur_payment['entity']->processing_type,
				'payment' => &$cur_payment,
				'ticket' => &$this
			));
			if (!in_array($cur_payment['status'], array('approved', 'declined')))
				$return = false;
		}
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
		$this->payments = (array) $sale->payments;
		$payment_total = 0;
		// TODO: Update $sale->returned_total when return is processed.
		$sale_total = $sale->total - (float) $sale->returned_total;
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
			if ($payment_total >= $sale_total) {
				unset($this->payments[$key]);
				continue;
			}
			// Reduce the amount to however much is left to return the sale
			// total.
			$cur_payment['amount'] -= ($payment_total + $cur_payment['amount']) - $sale_total;
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
		if (!is_array($this->products)) {
			pines_notice('Sale has no products');
			return false;
		}
		if (!is_array($this->payments))
			return false;
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
		// Go through each product, and find/create corresponding stock entries.
		foreach ($this->products as &$cur_product) {
			if ($cur_product['entity']->stock_type == 'non_stocked') {
				$cur_product['stock_entities'] = array();
			} else {
				if (isset($this->sale)) {
					$cur_product['stock_entities'] = array_slice($cur_product['stock_entities'], 0, (int) $cur_product['quantity']);
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

		if (isset($this->sale)) {
			// Go through each product, and mark the sold quantity in the sale.
			foreach ($this->products as &$cur_product) {
				if (!isset($this->sale->products[$cur_product['sale_key']]['returned_quantity']))
					$this->sale->products[$cur_product['sale_key']]['returned_quantity'] = 0;
				$this->sale->products[$cur_product['sale_key']]['returned_quantity'] += $cur_product['quantity'];
			}
			unset($cur_product);
			$return = $return && $this->sale->save();
		}

		// Complete the transaction.
		if ($return) {
			// Make a transaction entry.
			$tx = com_sales_tx::factory('sale_tx');

			$this->status = 'processed';
			$tx->type = 'returned';

			// Make sure we have a GUID before saving the tx.
			if (!($this->guid))
				$return = $return && $this->save();

			$tx->ticket = $this;
			$return = $return && $tx->save();
		}

		$this->process_date = time();

		return $return;
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
		}
		unset($cur_product);
		foreach ($products as $key => &$cur_product) {
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
			$return = $return && $this->sale->save();
		return $return;
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
			// Call the payment processing.
			$pines->com_sales->call_payment_process(array(
				'action' => 'return',
				'name' => $cur_payment['entity']->processing_type,
				'payment' => &$cur_payment,
				'ticket' => &$this
			));
			// If the payment went through, record it, if it didn't and it
			// wasn't declined, consider it a failure.
			if ($cur_payment['status'] != 'tendered') {
				if ($cur_payment['status'] != 'declined')
					$return = false;
			} else {
				// If it was tendered, add to the amount tendered.
				$amount_tendered += (float) $cur_payment['amount'];
				// And if there is a sale, add it to the sale's returned value.
				if (isset($this->sale))
					$this->sale->returned_total += (float) $cur_payment['amount'];
				// Make a transaction entry.
				$tx = com_sales_tx::factory('payment_tx');
				$tx->type = 'payment_returned';
				$tx->amount = (float) $cur_payment['amount'];
				$tx->ref = $cur_payment['entity'];

				// Make sure we have a GUID before saving the tx.
				if (!($this->guid))
					$return = $return && $this->save();

				$tx->ticket = $this;
				$return = $return && $tx->save();
			}
		}
		$amount_due = $total - $amount_tendered;
		if ($amount_due < 0.00)
			$amount_due = 0.00;
		$this->amount_tendered = $amount_tendered;
		$this->amount_due = $amount_due;
		if (isset($this->sale))
			$return = $return && $this->sale->save();
		return $return;
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
					'data' => array('enabled', true),
					'tag' => array('com_sales', 'tax_fee')
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
		// Go through each product, calculating its line total and fees.
		foreach ($this->products as &$cur_product) {
			$price = (float) $cur_product['price'];
			$qty = (int) $cur_product['quantity'];
			$discount = $cur_product['discount'];
			if ($cur_product['entity']->discountable && $discount != "") {
				$discount_price = $price;
				if (preg_match('/^\$-?\d+(\.\d+)?$/', $discount)) {
					// This is an exact discount.
					$discount = (float) preg_replace('/[^0-9.-]/', '', $discount);
					$discount_price = $price - $discount;
				} elseif (preg_match('/^-?\d+(\.\d+)?%$/', $discount)) {
					// This is a percentage discount.
					$discount = (float) preg_replace('/[^0-9.-]/', '', $discount);
					$discount_price = $price - ($price * ($discount / 100));
				}
				// Check that the discount doesn't lower the item's price below the floor.
				if ($cur_product['entity']->floor && $pines->com_sales->round($discount_price, $pines->config->com_sales->dec) < $pines->com_sales->round($cur_product['entity']->floor, $pines->config->com_sales->dec)) {
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
			if (!$cur_product['entity']->tax_exempt) {
				// Add location taxes.
				foreach ($tax_fees as $cur_tax_fee) {
					if ($cur_tax_fee->type == 'percentage') {
						$taxes += ($cur_tax_fee->rate / 100) * $line_total;
					} elseif ($cur_tax_fee->type == 'flat_rate') {
						$taxes += $cur_tax_fee->rate * $qty;
					}
				}
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
			$item_fees += (float) $cur_item_fees;
			$subtotal += (float) $line_total;
			$cur_product['line_total'] = $pines->com_sales->round($line_total, $pines->config->com_sales->dec);
			$cur_product['fees'] = $pines->com_sales->round($cur_item_fees, $pines->config->com_sales->dec);
		}
		// The total can now be calculated.
		$total = $subtotal + $item_fees + $taxes;
		$this->subtotal = $pines->com_sales->round($subtotal, $pines->config->com_sales->dec);
		$this->item_fees = $pines->com_sales->round($item_fees, $pines->config->com_sales->dec);
		$this->taxes = $pines->com_sales->round($taxes, $pines->config->com_sales->dec);
		$this->total = $pines->com_sales->round($total, $pines->config->com_sales->dec);
		return true;
	}
}

?>