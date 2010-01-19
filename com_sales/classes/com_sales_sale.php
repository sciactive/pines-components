<?php
/**
 * com_sales_sale class.
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
 * A sale.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_sale extends entity {
	/**
	 * Load a sale.
	 * @param int $id The ID of the sale to load, 0 for a new sale.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'sale');
		// Defaults.
		$this->products = array();
		$this->payments = array();
		if ($id > 0) {
			global $config;
			$entity = $config->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->parent = $entity->parent;
			$this->tags = $entity->tags;
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
	 * Delete the sale.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted sale $this->name.", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the sale.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $config;
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_sales', 'form_sale', 'content');
		$module->entity = $this;
		$module->tax_fees = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'tax_fee'), 'class' => com_sales_tax_fee));
		if (!is_array($module->tax_fees)) {
			$module->tax_fees = array();
		}
		foreach ($module->tax_fees as $key => $cur_tax_fee) {
			if (!$cur_tax_fee->enabled) {
				unset($module->tax_fees[$key]);
			}
		}
		$module->payment_types = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'payment_type'), 'class' => com_sales_payment_type));
		if (!is_array($module->payment_types)) {
			$module->payment_types = array();
		}
		foreach ($module->payment_types as $key => $cur_payment_type) {
			if (!$cur_payment_type->enabled) {
				unset($module->payment_types[$key]);
			}
		}

		return $module;
	}

	/**
	 * Print a receipt of the sale.
	 * @return module The form's module.
	 */
	function print_receipt($id = NULL) {
		global $config;
		$module = new module('com_sales', 'receipt_sale', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Approve each payment.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function approve_payments() {
		global $config;
		$return = true;
		// Go through each payment, and process its approval.
		foreach ($this->payments as &$cur_payment) {
			// If its already approved or tendered, skip it.
			if (in_array($cur_payment['status'], array('approved', 'declined', 'tendered')))
				continue;
			// Check minimum and maximum values.
			if ((float) $cur_payment['amount'] < $cur_payment['entity']->minimum) {
				display_notice("The payment type [{$cur_payment['entity']->name}] requires a minimum payment of {$cur_payment['entity']->minimum}.");
				$cur_payment['status'] = 'declined';
				$return = false;
				continue;
			}
			if (isset($cur_payment['entity']->maximum) && (float) $cur_payment['amount'] > $cur_payment['entity']->maximum) {
				display_notice("The payment type [{$cur_payment['entity']->name}] requires a maximum payment of {$cur_payment['entity']->maximum}.");
				$cur_payment['status'] = 'declined';
				$return = false;
				continue;
			}
			// Call the payment processing for approval.
			$config->run_sales->call_payment_process(array(
				'action' => 'approve',
				'name' => $cur_payment['entity']->processing_type,
				'payment' => &$cur_payment,
				'sale' => &$this
			));
			if (!in_array($cur_payment['status'], array('approved', 'declined')))
				$return = false;
		}
		return $return;
	}

	/**
	 * Process each payment.
	 *
	 * This process updates "amount_tendered", "amount_due", and "change" on the
	 * sale itself.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function tender_payments() {
		global $config;
		if (!is_array($this->payments))
			$this->payments = array();
		if (!is_numeric($this->total))
			return false;
		$total = (float) $this->total;
		$amount_tendered = (float) $this->amount_tendered;
		$amount_due = 0.00;
		$change = 0.00;
		$return = true;
		foreach ($this->payments as &$cur_payment) {
			// If its already tendered, skip it.
			if (in_array($cur_payment['status'], array('declined', 'tendered')))
				continue;
			// If its not approved, sale can't be tendered.
			if ($cur_payment['status'] != 'approved') {
				$return = false;
				continue;
			}
			// Call the payment processing.
			$config->run_sales->call_payment_process(array(
				'action' => 'tender',
				'name' => $cur_payment['entity']->processing_type,
				'payment' => &$cur_payment,
				'sale' => &$this
			));
			// If the payment went through, record it, if it didn't and it
			// wasn't declined, consider it a failure.
			if ($cur_payment['status'] != 'tendered') {
				if ($cur_payment['status'] != 'declined')
					$return = false;
			} else {
				// If it was tendered, add to the amount tendered.
				$amount_tendered += (float) $cur_payment['amount'];
				// Make a transaction entry.
				$tx = com_sales_tx::factory('com_sales', 'transaction', 'payment_tx');
				$tx->type = 'payment_received';
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
		if ($amount_due < 0.00) {
			$change = abs($amount_due);
			$amount_due = 0.00;
		}
		$this->amount_tendered = $amount_tendered;
		$this->amount_due = $amount_due;
		$this->change = $change;
		return $return;
	}

	/**
	 * Complete the sale.
	 *
	 * This process creates payment transaction entries for each payment and any
	 * change given.
	 *
	 * A sale transaction is created, and the sale's status is changed to
	 * 'paid'.
	 *
	 * @return bool True on success, false on any failure.
	 */
	public function complete() {
		global $config;
		if (!is_array($this->payments))
			return false;
		// Keep track of the whole process.
		$return = true;
		if (!$this->approve_payments()) {
			display_notice('The sale cannot be completed until all payments have been approved or declined.');
			return false;
		}
		if (!$this->tender_payments()) {
			display_notice('All payments have not been tendered. The sale was not completed. Please check the status on each payment.');
			return false;
		}
		if (!isset($this->amount_due) || $this->amount_due > 0) {
			display_notice('The sale cannot be completed while there is still an amount due.');
			return false;
		}
		if ($this->change > 0.00) {
			$change_type = $config->entity_manager->get_entity(array('data' => array('change_type' => true), 'tags' => array('com_sales', 'payment_type'), 'class' => com_sales_payment_type));
			if (is_null($change_type)) {
				display_notice('Change is due to be given, but no payment type has been set to give change.');
				return false;
			}
		}

		// Process the change.
		if (!$this->change_given && $this->change > 0.00) {
			$config->run_sales->call_payment_process(array(
				'action' => 'change',
				'name' => $change_type->processing_type,
				'sale' => &$this
			));
			if (!$this->change_given) {
				display_notice('Change is due, but the payment type designated to give change declined the request.');
				return false;
			} else {
				// Make a transaction entry.
				$tx = com_sales_tx::factory('com_sales', 'transaction', 'payment_tx');
				$tx->type = 'change_given';
				$tx->amount = (float) $this->change;
				$tx->ref = $change_type;

				// Make sure we have a GUID before saving the tx.
				if (!($this->guid))
					$return = $return && $this->save();

				$tx->ticket = $this;
				$return = $return && $tx->save();
			}
		}

		// Complete the transaction.
		if ($return) {
			// Make a transaction entry.
			$tx = com_sales_tx::factory('com_sales', 'transaction', 'sale_tx');

			$this->status = 'paid';
			$tx->type = 'paid';

			// Make sure we have a GUID before saving the tx.
			if (!($this->guid))
				$return = $return && $this->save();

			$tx->ticket = $this;
			$return = $return && $tx->save();
		}

		return $return;
	}

	/**
	 * Invoice the sale.
	 *
	 * This process will remove any sold items from stock. Payment is not
	 * considered.
	 *
	 * A sale transaction is created, and the sale's status is changed to
	 * 'invoiced'.
	 *
	 * @return bool True on success, false on any failure.
	 */
	public function invoice() {
		global $config;
		if (!is_array($this->products)) {
			display_notice('Sale has no products');
			return false;
		}
		// Keep track of the whole process.
		$return = true;
		// These will be searched through to match products to stock entries.
		$stock_entries = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
		if (!is_array($stock_entries))
			$stock_entries = array();
		if ($config->run_sales->com_customer) {
			foreach ($this->products as &$cur_product) {
				if (!$cur_product['entity'] || ($cur_product['entity']->require_customer && !$this->customer)) {
					display_notice('One of the products on this sale requires a customer. Please select a customer for this sale before invoicing.');
					return false;
				}
			}
		}
		unset($cur_product);
		// Calculate and save the sale's totals.
		if (!$this->total()) {
			display_notice('Couldn\'t total sale.');
			return false;
		}
		// Go through each product, and find corresponding stock entries.
		foreach ($this->products as &$cur_product) {
			// Find the stock entry.
			// TODO: Ship to customer from different stock (Warehouse).
			if ($cur_product['entity']->stock_type == 'non_stocked') {
				$cur_product['stock_entities'] = array();
			} else {
				$stock_entities = array();
				for ($i = 0; $i < $cur_product['quantity']; $i++) {
					$found = false;
					foreach($stock_entries as $key => $cur_stock) {
						if (($cur_stock->status != 'available') ||
							(!$_SESSION['user']->ingroup($cur_stock->location->guid)) ||
							(!$cur_product['entity']->is($cur_stock->product)) ||
							($cur_product['entity']->serialized && ($cur_product['serial'] != $cur_stock->serial))) {
							continue;
						}
						// One was found, so save it then take it out of our search stock.
						$found = true;
						$stock_entities[] = clone $cur_stock;
						unset($stock_entries[$key]);
						break;
					}
					if (!$found) {
						if ($cur_product['entity']->stock_type != 'stock_optional') {
							// It wasn't found, and its not optional.
							display_notice("Product with SKU [{$cur_product['sku']}]".($cur_product['entity']->serialized ? " and serial [{$cur_product['serial']}]" : " and quantity {$cur_product['quantity']}")." is not in local stock.".($cur_product['entity']->serialized ? '' : ' Found '.count($stock_entities).'.'));
							return false;
						} else {
							// It wasn't found, but it's optional, so mark this item as shipped.
							// TODO: For multiple quantity items, mark how many need to be shipped.
							$cur_product['delivery'] = 'shipped';
						}
					}
				}
				$cur_product['stock_entities'] = $stock_entities;
			}
		}
		unset($cur_product);
		// Go through each product, calling actions, and marking their stock as sold.
		foreach ($this->products as &$cur_product) {
			// Call product actions for all products without stock entries.
			$i = $cur_product['quantity'] - count($cur_product['stock_entities']);
			if ($i > 0) {
				$config->run_sales->call_product_actions(array(
					'type' => 'sold',
					'product' => $cur_product['entity'],
					'sale' => $this
				), $i);
			}
			// Remove stock from inventory.
			if (is_array($cur_product['stock_entities'])) {
				foreach ($cur_product['stock_entities'] as &$cur_stock) {
					if ($cur_product['delivery'] == 'in-store') {
						$return = $return && $cur_stock->remove($this, 'sold_at_store') && $cur_stock->save();
					} else {
						$return = $return && $cur_stock->remove($this, 'sold_pending', $cur_stock->location) && $cur_stock->save();
					}
					$config->run_sales->call_product_actions(array(
						'type' => 'sold',
						'product' => $cur_product['entity'],
						'stock_entry' => $cur_stock,
						'sale' => $this
					));
				}
			}
		}
		unset($cur_product);

		// Make a transaction entry.
		$tx = com_sales_tx::factory('com_sales', 'transaction', 'sale_tx');

		$this->status = 'invoiced';
		$tx->type = 'invoiced';

		// Make sure we have a GUID before saving the tx.
		if (!($this->guid))
			$return = $return && $this->save();
		
		$tx->ticket = $this;
		$return = $return && $tx->save();

		return $return;
	}

	/**
	 * Calculate and set the sale's totals.
	 *
	 * This process adds "line_total" and "fees" to each product on the sale,
	 * and adds "subtotal", "item_fees", "taxes", and "total" to the sale itself.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function total() {
		global $config;
		if (!is_array($this->products))
			return false;
		// We need a list of taxes and fees.
		$tax_fees = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'tax_fee')));
		if (!is_array($tax_fees)) {
			$tax_fees = array();
		}
		foreach ($tax_fees as $key => $cur_tax_fee) {
			if (!$cur_tax_fee->enabled) {
				// It isn't even enabled, so remove it now.
				unset($tax_fees[$key]);
				continue;
			}
			foreach($cur_tax_fee->locations as $cur_location) {
				// If we're in one of its groups, don't remove it.
				if ($_SESSION['user']->ingroup($cur_location->guid))
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
					$discount = floatval(preg_replace('/[^0-9.-]/', '', $discount));
					$discount_price = $price - $discount;
				} elseif (preg_match('/^-?\d+(\.\d+)?%$/', $discount)) {
					// This is a percentage discount.
					$discount = floatval(preg_replace('/[^0-9.-]/', '', $discount));
					$discount_price = $price - ($price * ($discount / 100));
				}
				// Check that the discount doesn't lower the item's price below the floor.
				if ($cur_product['entity']->floor && $config->run_sales->round($discount_price, $config->com_sales->dec) < $config->run_sales->round($cur_product['entity']->floor, $config->com_sales->dec)) {
					display_notice("The discount on {$cur_product['entity']->name} lowers the product's price below the limit. The discount was removed.");
					$discount = $cur_product['discount'] = '';
				} else {
					$price = $discount_price;
				}
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
			$cur_product['line_total'] = $config->run_sales->round($line_total, $config->com_sales->dec);
			$cur_product['fees'] = $config->run_sales->round($cur_item_fees, $config->com_sales->dec);
		}
		// The total can now be calculated.
		$total = $subtotal + $item_fees + $taxes;
		$this->subtotal = $config->run_sales->round($subtotal, $config->com_sales->dec);
		$this->item_fees = $config->run_sales->round($item_fees, $config->com_sales->dec);
		$this->taxes = $config->run_sales->round($taxes, $config->com_sales->dec);
		$this->total = $config->run_sales->round($total, $config->com_sales->dec);
		return true;
	}
}

?>