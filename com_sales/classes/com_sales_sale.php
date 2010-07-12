<?php
/**
 * com_sales_sale class.
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
	 * @return com_sales_sale The new instance.
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
	 * Calculate and add commission to the employee.
	 */
	public function add_commission() {
		global $pines;
		if ($this->added_commission || !$pines->config->com_sales->com_hrm)
			return;
		$this->added_commission = true;
		$user = isset($this->user) ? $this->user : $_SESSION['user'];
		if (!isset($user))
			return;
		$user->refresh();
		// Go through each product, adding commission.
		foreach ($this->products as &$cur_product) {
			// Call product actions for all products without stock entries.
			if (!$cur_product['entity']->commissions)
				continue;
			$cur_product['commission'] = 0;
			foreach ($cur_product['entity']->commissions as $cur_commission) {
				if (!$user->in_group($cur_commission['group']))
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
			if ((array) $user->commissions !== $user->commissions)
				$user->commissions = array();
			$user->commissions[] = array(
				'date' => time(),
				'amount' => $cur_product['commission'] * $cur_product['quantity'],
				'ticket' => $this,
				'product' => $cur_product['entity']
			);
		}
		unset($cur_product);
		$user->save();
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
		global $pines;
		if ($this->status == 'paid' || $this->status == 'voided')
			return true;
		if (empty($this->products)) {
			pines_notice('Sale has no products');
			return false;
		}
		// Keep track of the whole process.
		$return = true;
		if ($this->status != 'invoiced') {
			if ($pines->config->com_sales->com_customer && !isset($this->customer)) {
				foreach ($this->products as &$cur_product) {
					if (!$cur_product['entity'] || ($cur_product['entity']->require_customer)) {
						pines_notice('One of the products on this sale requires a customer. Please select a customer for this sale before invoicing.');
						return false;
					}
				}
				unset($cur_product);
			}
			// Calculate and save the sale's totals.
			if (!$this->total()) {
				pines_notice('Couldn\'t total sale.');
				return false;
			}
		}
		if (empty($this->payments) && $this->total > 0) {
			pines_notice('Sale has no payments');
			return false;
		}
		// Check stock before tendering payments.
		// We need to see if stock is already removed here so we don't look it up again.
		// The sale can be invoiced and tendered by different users with different remove_stock settings.
		if (!$this->removed_stock) {
			// Look up stock.
			if (!$this->get_stock())
				return false;
		}
		if (!$this->approve_payments()) {
			pines_notice('The sale cannot be completed until all payments have been approved or declined.');
			return false;
		}
		if (!$this->tender_payments()) {
			pines_notice('All payments have not been tendered. The sale was not completed. Please check the status on each payment.');
			return false;
		}
		if (!isset($this->amount_due) || $this->amount_due > 0) {
			pines_notice('The sale cannot be completed while there is still an amount due.');
			return false;
		}
		if ($this->change > 0.00) {
			$change_type = $pines->entity_manager->get_entity(
					array('class' => com_sales_payment_type),
					array('&',
						'data' => array(
							array('change_type', true),
							array('enabled', true)
						),
						'tag' => array('com_sales', 'payment_type')
					)
				);
			if (!isset($change_type)) {
				pines_notice('Change is due to be given, but no payment type has been set to give change.');
				return false;
			}
		}

		// Process the change.
		if (!$this->change_given && $this->change > 0.00) {
			$pines->com_sales->call_payment_process(array(
				'action' => 'change',
				'name' => $change_type->processing_type,
				'ticket' => &$this
			));
			if (!$this->change_given) {
				pines_notice('Change is due, but the payment type designated to give change declined the request.');
				return false;
			} else {
				// Make a transaction entry.
				$tx = com_sales_tx::factory('payment_tx');
				$tx->type = 'change_given';
				$tx->amount = (float) $this->change;
				$tx->ref = $change_type;

				// Make sure we have a GUID before saving the tx.
				if (!($this->guid))
					$return = $this->save() && $return;

				$tx->ticket = $this;
				$return = $tx->save() && $return;
			}
		}

		if (!$this->removed_stock) {
			// Remove stock.
			$stock_result = $this->remove_stock();
			$return = $return && $stock_result;
			if (!$stock_result)
				pines_notice('Not all stock could be removed from inventory while tendering. Please check that all stock was correctly entered.');
		}
		if (!$this->performed_actions) {
			// Perform actions.
			$this->perform_actions();
		}
		if (!$this->added_commission) {
			// Add commission.
			$this->add_commission();
		}

		// Complete the transaction.
		if ($return) {
			// Make a transaction entry.
			$tx = com_sales_tx::factory('sale_tx');

			$this->status = 'paid';
			$tx->type = 'paid';

			// Make sure we have a GUID before saving the tx.
			if (!($this->guid))
				$return = $this->save() && $return;

			$tx->ticket = $this;
			$return = $tx->save() && $return;
		}

		$this->tender_date = time();

		return ($this->save() && $return);
	}

	/**
	 * Delete the sale.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted sale $this->id.", 'notice');
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
	 * Email a receipt of the sale to the customer's email.
	 *
	 * @return bool True on success, false on failure.
	 */
	function email_receipt() {
		global $pines;
		if (empty($this->customer->email))
			return false;
		$module = new module('com_sales', 'sale/receipt');
		$module->entity = $this;
		// TODO: Allow more customization for email subject/content.
		$subject = 'Receipt for ' . $this->customer->name;
		$content = "<style type=\"text/css\">/* <![CDATA[ */\n";
		$content .= file_get_contents('system/css/pform.css');
		$content .= "\n/* ]]> */</style>";
		$content .= $module->render();

		$mail = com_mailer_mail::factory($pines->config->com_sales->email_from_address, $this->customer->email, $subject, $content);
		if ($mail->send()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Look up and attach stock entries for products.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function get_stock() {
		global $pines;
		// Go through each product, and find corresponding stock entries.
		foreach ($this->products as &$cur_product) {
			// Find the stock entry.
			// TODO: Ship to customer from different stock (Warehouse).
			if ($cur_product['entity']->stock_type == 'non_stocked') {
				$cur_product['stock_entities'] = array();
			} else {
				$stock_entities = array();
				$guids = array();
				for ($i = 0; $i < $cur_product['quantity']; $i++) {
					$selector = array('&',
							'data' => array(
								array('available', true)
							),
							'ref' => array(
								array('product', $cur_product['entity'])
							),
							'tag' => array('com_sales', 'stock')
						);
					if (isset($this->group)) {
						$selector['ref'][] = array('location', $this->group);
					} elseif (isset($_SESSION['user']->group)) {
						$selector['ref'][] = array('location', $_SESSION['user']->group);
					}
					if ($cur_product['entity']->serialized)
						$selector['data'][] = array('serial', $cur_product['serial']);
					if (!$guids) {
						$stock_entry = $pines->entity_manager->get_entity(array('class' => com_sales_stock), $selector);
					} else {
						$stock_entry = $pines->entity_manager->get_entity(
								array('class' => com_sales_stock),
								$selector,
								array('!&',
									'guid' => $guids
								)
							);
					}
					if (isset($stock_entry)) {
						$stock_entities[] = $stock_entry;
						$guids[] = $stock_entry->guid;
					} else {
						if ($cur_product['entity']->stock_type != 'stock_optional') {
							// It wasn't found, and its not optional.
							pines_notice("Product with SKU [{$cur_product['sku']}]".($cur_product['entity']->serialized ? " and serial [{$cur_product['serial']}]" : " and quantity {$cur_product['quantity']}")." is not in local stock.".($cur_product['entity']->serialized ? '' : ' Found '.count($stock_entities).'.'));
							return false;
						} else {
							// It wasn't found, but it's optional, so mark this item as shipped if it's marked in-store.
							// TODO: For multiple quantity items, mark how many need to be shipped.
							if ($cur_product['delivery'] == 'in-store')
								$cur_product['delivery'] = 'shipped';
						}
					}
				}
				$cur_product['stock_entities'] = $stock_entities;
			}
		}
		unset($cur_product);
		return true;
	}

	/**
	 * Invoice the sale.
	 *
	 * This process may remove any sold items from stock. Payment is not
	 * considered.
	 *
	 * It doesn't do anything that complete() doesn't do, and complete() can be
	 * called without calling invoice().
	 *
	 * A sale transaction is created, and the sale's status is changed to
	 * 'invoiced'.
	 *
	 * @return bool True on success, false on any failure.
	 */
	public function invoice() {
		global $pines;
		if ($this->status == 'invoiced' || $this->status == 'voided')
			return true;
		if (!is_array($this->products)) {
			pines_notice('Sale has no products');
			return false;
		}
		// Keep track of the whole process.
		$return = true;
		if ($pines->config->com_sales->com_customer && !isset($this->customer)) {
			foreach ($this->products as &$cur_product) {
				if (!$cur_product['entity'] || ($cur_product['entity']->require_customer)) {
					pines_notice('One of the products on this sale requires a customer. Please select a customer for this sale before invoicing.');
					return false;
				}
			}
			unset($cur_product);
		}
		// Calculate and save the sale's totals.
		if (!$this->total()) {
			pines_notice('Couldn\'t total sale.');
			return false;
		}

		if ($pines->config->com_sales->remove_stock == 'invoice') {
			// Look up stock.
			if (!$this->get_stock())
				return false;
			// Remove stock.
			$stock_result = $this->remove_stock();
			$return = $return && $stock_result;
			if (!$stock_result)
				pines_notice('Not all stock could be removed from inventory while invoicing. Please check that all stock was correctly entered.');
		}
		if ($pines->config->com_sales->perform_actions == 'invoice') {
			// Perform actions.
			$this->perform_actions();
		}
		if ($pines->config->com_sales->add_commission == 'invoice') {
			// Add commission.
			$this->add_commission();
		}

		// Make a transaction entry.
		$tx = com_sales_tx::factory('sale_tx');

		$this->status = 'invoiced';
		$tx->type = 'invoiced';

		// Make sure we have a GUID before saving the tx.
		if (!($this->guid))
			$return = $this->save() && $return;

		$tx->ticket = $this;
		$return = $tx->save() && $return;

		$this->invoice_date = time();

		return ($this->save() && $return);
	}

	/**
	 * Run the product actions associated with the products on this sale.
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
					'type' => 'sold',
					'product' => $cur_product['entity'],
					'ticket' => $this,
					'serial' => $cur_product['serial'],
					'delivery' => $cur_product['delivery'],
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
					'type' => 'sold',
					'product' => $cur_product['entity'],
					'stock_entry' => $cur_stock,
					'ticket' => $this,
					'serial' => $cur_product['serial'],
					'delivery' => $cur_product['delivery'],
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
	 * Print a form to edit the sale.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'sale/form', 'content');
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
		if (isset($this->guid)) {
			$module->returns = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_return),
					array('&',
						'ref' => array('sale', $this),
						'tag' => array('com_sales', 'return')
					)
				);
		} else {
			$module->returns = array();
		}

		return $module;
	}

	/**
	 * Print a receipt of the sale.
	 * @return module The form's module.
	 */
	function print_receipt() {
		global $pines;

		$module = new module('com_sales', 'sale/receipt', 'content');
		$module->entity = $this;

		if (isset($this->customer->email))
			$pines->com_sales->inform('Email Receipt', 'Send a Copy', 'Send a copy of this receipt to the customer\'s e-mail address.', pines_url('com_sales', 'sale/sendreceipt', array('id' => $this->guid)));

		return $module;
	}

	/**
	 * Remove the stock on this sale from current inventory.
	 * @return bool True on success, false on any failure.
	 */
	public function remove_stock() {
		if ($this->removed_stock)
			return false;
		$this->removed_stock = true;
		// Keep track of the whole process.
		$return = true;
		// Go through each product, marking its stock as sold.
		foreach ($this->products as &$cur_product) {
			// Remove stock from inventory.
			if (!is_array($cur_product['stock_entities']))
				continue;
			foreach ($cur_product['stock_entities'] as &$cur_stock) {
				if ($cur_product['delivery'] == 'shipped') {
					$return = $cur_stock->remove('sold_pending_shipping', $this, $cur_stock->location) && $cur_stock->save() && $return;
				} elseif ($cur_product['delivery'] == 'pick-up') {
					$return = $cur_stock->remove('sold_pending_pickup', $this, $cur_stock->location) && $cur_stock->save() && $return;
				} else {
					$return = $cur_stock->remove('sold_at_store', $this) && $cur_stock->save() && $return;
				}
			}
		}
		unset($cur_product);
		return ($this->save() && $return);
	}

	/**
	 * Save the sale.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $pines;
		if (!isset($this->status))
			$this->status = 'quoted';
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_sales_sale');
		return parent::save();
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
		global $pines;
		if (!is_array($this->payments))
			$this->payments = array();
		if (!is_numeric($this->total))
			return false;
		$this->tendered_payments = true;
		$total = (float) $this->total;
		$amount_tendered = (float) $this->amount_tendered;
		$amount_due = 0.00;
		$change = 0.00;
		$return = true;
		foreach ($this->payments as &$cur_payment) {
			// If it's already tendered, skip it.
			if (in_array($cur_payment['status'], array('declined', 'tendered')))
				continue;
			// If it's not approved, sale can't be tendered.
			if ($cur_payment['status'] != 'approved') {
				$return = false;
				continue;
			}
			// Call the payment processing.
			$pines->com_sales->call_payment_process(array(
				'action' => 'tender',
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
				// Make a transaction entry.
				$tx = com_sales_tx::factory('payment_tx');
				$tx->type = 'payment_received';
				$tx->amount = (float) $cur_payment['amount'];
				$tx->ref = $cur_payment['entity'];

				// Make sure we have a GUID before saving the tx.
				if (!($this->guid))
					$return = $this->save() && $return;

				$tx->ticket = $this;
				$return = $tx->save() && $return;
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
		return ($this->save() && $return);
	}

	/**
	 * Calculate and set the sale's totals.
	 *
	 * This process adds "line_total" and "fees" to each product on the sale,
	 * and adds "subtotal", "item_fees", "taxes", and "total" to the sale
	 * itself.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function total() {
		global $pines;
		if (!is_array($this->products) || in_array($this->status, array('invoiced', 'paid', 'voided')))
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
			$cur_product['line_total'] = $pines->com_sales->round($line_total);
			$cur_product['fees'] = $pines->com_sales->round($cur_item_fees);
		}
		unset($cur_product);
		// The total can now be calculated.
		$total = $subtotal + $item_fees + $taxes;
		$this->subtotal = $pines->com_sales->round($subtotal);
		$this->item_fees = $pines->com_sales->round($item_fees);
		$this->taxes = $pines->com_sales->round($taxes);
		$this->total = $pines->com_sales->round($total);
		return true;
	}

	/**
	 * Void the sale.
	 *
	 * A sale transaction is created, and the sale's status is changed to
	 * 'voided'.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function void() {
		global $pines;
		if ($this->status == 'voided')
			return true;
		// Check if this sale is attached to any returns. If so, it cannot be voided.
		$attached_return = $pines->entity_manager->get_entity(
				array('class' => com_sales_return, 'skip_ac' => true),
				array('&',
					'ref' => array('sale', $this),
					'tag' => array('com_sales', 'return')
				)
			);
		if (isset($attached_return)) {
			pines_notice('This sale cannot be voided, because it is attached to a return.');
			return false;
		}
		// Keep track of the whole process.
		$return = true;
		if ($this->removed_stock) {
			// Go through each product, returning its stock.
			foreach ($this->products as &$cur_product) {
				// Return stock to inventory.
				if (!is_array($cur_product['stock_entities']))
					continue;
				foreach ($cur_product['stock_entities'] as &$cur_stock) {
					$last_tx = $pines->entity_manager->get_entity(
							array('reverse' => true, 'class' => com_sales_stock),
							array('&',
								'data' => array('type', 'removed'),
								'ref' => array('ref', $this),
								'tag' => array('com_sales', 'transaction', 'stock_tx')
							)
						);
					if ($last_tx) {
						$return = $cur_stock->receive('sold_at_store', $this, $last_tx->old_location) && $cur_stock->save() && $return;
					} else {
						$return = $cur_stock->receive('sold_at_store', $this) && $cur_stock->save() && $return;
					}
				}
			}
			unset($cur_product);
		}
		if ($this->performed_actions) {
			// Go through each product, calling actions.
			foreach ($this->products as &$cur_product) {
				// Call product actions for all products without stock entries.
				$i = $cur_product['quantity'] - count($cur_product['stock_entities']);
				if ($i > 0) {
					$pines->com_sales->call_product_actions(array(
						'type' => 'voided',
						'product' => $cur_product['entity'],
						'ticket' => $this,
						'serial' => $cur_product['serial'],
						'delivery' => $cur_product['delivery'],
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
						'type' => 'voided',
						'product' => $cur_product['entity'],
						'stock_entry' => $cur_stock,
						'ticket' => $this,
						'serial' => $cur_product['serial'],
						'delivery' => $cur_product['delivery'],
						'price' => $cur_product['price'],
						'discount' => $cur_product['discount'],
						'line_total' => $cur_product['line_total'],
						'fees' => $cur_product['fees']
					));
				}
			}
			unset($cur_product);
		}
		if ($this->tendered_payments) {
			// Go through each payment, voiding it.
			foreach ($this->payments as &$cur_payment) {
				// If it's not tendered, skip it.
				if ($cur_payment['status'] != 'tendered')
					continue;
				// Call the payment processing.
				$pines->com_sales->call_payment_process(array(
					'action' => 'void',
					'name' => $cur_payment['entity']->processing_type,
					'payment' => &$cur_payment,
					'ticket' => &$this
				));
				// If the payment was voided, record it, if not, consider it a
				// failure.
				if ($cur_payment['status'] != 'voided') {
					$return = false;
				} else {
					// Make a transaction entry.
					$tx = com_sales_tx::factory('payment_tx');
					$tx->type = 'payment_voided';
					$tx->amount = (float) $cur_payment['amount'];
					$tx->ref = $cur_payment['entity'];

					// Make sure we have a GUID before saving the tx.
					if (!($this->guid))
						$return = $this->save() && $return;

					$tx->ticket = $this;
					$return = $tx->save() && $return;
				}
			}
			unset($cur_payment);
		}
		if ($this->added_commission && is_array($this->user->commissions)) {
			foreach ($this->user->commissions as &$cur_commission) {
				if ($this->is($cur_commission['ticket'])) {
					$cur_commission['note'] = "Sale was voided. Amount: \${$cur_commission['amount']}";
					$cur_commission['amount'] = 0;
				}
			}
			unset($cur_commission);
			$this->user->save();
		}

		// Complete the transaction.
		if ($return) {
			// Make a transaction entry.
			$tx = com_sales_tx::factory('sale_tx');

			$this->status = 'voided';
			$tx->type = 'voided';

			// Make sure we have a GUID before saving the tx.
			if (!($this->guid))
				$return = $this->save() && $return;

			$tx->ticket = $this;
			$return = $tx->save() && $return;
		}

		$this->void_date = time();
		return ($this->save() && $return);
	}
}

?>