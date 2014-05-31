<?php
/**
 * com_sales_sale class.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A sale.
 *
 * @package Components\sales
 */
class com_sales_sale extends entity {
	/**
	 * Load a sale.
	 * @param int $id The ID of the sale to load, 0 for a new sale.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'sale');
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
		$this->shipping_use_customer = true;
		$this->products = array();
		$this->payments = array();
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
	 * Return the entity helper module.
	 * @return module Entity helper module.
	 */
	public function helper() {
		return new module('com_sales', 'sale/helper');
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return "Sale $this->id";
			case 'type':
				return 'sale';
			case 'types':
				return 'sales';
			case 'url_view':
				if (gatekeeper('com_sales/editsale'))
					return pines_url('com_sales', 'sale/receipt', array('id' => $this->guid));
				break;
			case 'url_edit':
				if (gatekeeper('com_sales/editsale'))
					return pines_url('com_sales', 'sale/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listsales'))
					return pines_url('com_sales', 'sale/list');
				break;
			case 'icon':
				return 'picon-archive-insert';
		}
		return null;
	}

	/**
	 * Calculate and add commission to the employee(s).
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
						// Add a spiff per product sold.
						$cur_product['commission'] += $pines->com_sales->round((float) $cur_commission['amount'] * $cur_product['quantity']);
						break;
					case 'percent_price':
						// Add a percentage of the amount sold - specials.
						$cur_product['commission'] += $pines->com_sales->round(($cur_product['line_total'] - (float) $cur_product['specials_total']) * ( ((float) $cur_commission['amount']) / 100 ));
						break;
					case 'percent_line_total':
						// Add a percentage of the amount sold.
						$cur_product['commission'] += $pines->com_sales->round($cur_product['line_total'] * ( ((float) $cur_commission['amount']) / 100 ));
						break;
				}
			}
			if ($cur_product['commission'] == 0)
				continue;
			// Add the commission to the user.
			if ((array) $cur_product['salesperson']->commissions !== $cur_product['salesperson']->commissions)
				$cur_product['salesperson']->commissions = array();
			$cur_product['salesperson']->commissions[] = array(
				'date' => time(),
				'amount' => $cur_product['commission'],
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
				'type' => 'charge',
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
	 * Print a form to change products.
	 *
	 * Uses a page override to only print the form.
	 *
	 * @return module The form's module.
	 */
	public function change_product_form() {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_sales', 'forms/change_product', 'content');
		$module->entity = $this;

		$pines->page->override_doc($module->render());
		return $module;
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
						'tag' => array('com_sales', 'payment_type'),
						'data' => array(
							array('change_type', true),
							array('enabled', true)
						)
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
				if (!isset($this->guid))
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
		if (!$this->added_commission && $pines->config->com_sales->use_commission) {
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
			if (!isset($this->guid))
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
	public function email_receipt() {
		global $pines;
		if (empty($this->customer->email))
			return false;
		$module = new module('com_sales', 'sale/receipt_email');
		$module->entity = $this;

		$macros = array(
			'receipt' => $module->render(),
			'sale_id' => htmlspecialchars($this->id),
			'sale_total' => htmlspecialchars($this->total),
		);
		
		if ($this->status =='voided') {
			return $pines->com_mailer->send_mail('com_sales/void_receipt', $macros, $this->customer);
		}
		
		return $pines->com_mailer->send_mail('com_sales/sale_receipt', $macros, $this->customer);
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
			if ($cur_product['delivery'] == 'warehouse' || $cur_product['entity']->stock_type == 'non_stocked') {
				// Warehouse and non stocked products don't need stock entries.
				// Warehouse stock is added later.
				$cur_product['stock_entities'] = array();
			} else {
				$stock_entities = array();
				$guids = array();
				for ($i = 0; $i < $cur_product['quantity']; $i++) {
					$selector = array('&',
							'tag' => array('com_sales', 'stock'),
							'data' => array(
								array('available', true)
							),
							'ref' => array(
								array('product', $cur_product['entity'])
							)
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
								array('!&',
									'guid' => $guids
								),
								$selector
							);
					}
					if (isset($stock_entry)) {
						$stock_entities[] = $stock_entry;
						$guids[] = $stock_entry->guid;
					} else {
						// It wasn't found.
						pines_notice("Product with SKU [{$cur_product['sku']}]".($cur_product['entity']->serialized ? " and serial [{$cur_product['serial']}]" : " and quantity {$cur_product['quantity']}")." is not in local stock.".($cur_product['entity']->serialized ? '' : ' Found '.count($stock_entities).'.'));
						return false;
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
		if (!isset($this->guid))
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
					'specials_total' => $cur_product['specials_total'],
					'fees' => $cur_product['fees'],
					'salesperson' => $cur_product['salesperson']
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
					'specials_total' => $cur_product['specials_total'],
					'fees' => $cur_product['fees'],
					'salesperson' => $cur_product['salesperson']
				));
			}
			unset($cur_stock);
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
		// Find eligible specials.
		if (!isset($this->elig_specials)) {
			$specials = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_special),
					array('&',
						'tag' => array('com_sales', 'special'),
						'data' => array(
							array('enabled', true),
							array('apply_to_all', true)
						)
					)
				);
			$module->specials = array();
			foreach ($specials as $cur_special) {
				if (!$cur_special->eligible())
					continue;
				$module->specials[] = $cur_special;
			}
		} else {
			$module->specials = $this->elig_specials;
		}
		if (isset($this->guid)) {
			$module->returns = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_return),
					array('&',
						'tag' => array('com_sales', 'return'),
						'ref' => array('sale', $this)
					)
				);
		} else {
			$module->returns = array();
		}

		return $module;
	}

	/**
	 * Print a receipt of the sale.
	 * @param bool $auto_print_ok Auto printing the receipt is ok.
	 * @return module The receipt's module.
	 */
	public function print_receipt($auto_print_ok = false) {
		$actions = new module('com_sales', 'sale/receiptactions', 'content');
		$actions->entity = $this;
		$module = new module('com_sales', 'sale/receipt', 'content');
		$module->entity = $this;
		$actions->auto_print_ok = $auto_print_ok;

		return $module;
	}

	/**
	 * Format a barcode for the receipt printer.
	 *
	 * @param string $text The barcode text.
	 * @return string The formatted barcode code.
	 * @access private
	 */
	private function receipt_format_barcode($text) {
		global $pines;
		// Barcode height.
		$barcode = chr(hexdec('1D')).'h'.chr(50);
		// Text below barcode.
		$barcode .= chr(hexdec('1D')).'H'.chr(2);
		// First barcode font.
		$barcode .= chr(hexdec('1D')).'f'.chr(0);
		// Width.
		$barcode .= chr(hexdec('1D')).'w'.chr(2 * (int) $pines->config->com_barcode->xres);
		// Barcode type and data.
		switch ($pines->config->com_barcode->type) {
			case 'I25':
				$barcode .= chr(hexdec('1D')).'k'.chr(70).chr(strlen("$text")).$text;
				break;
			case 'C39':
			default:
				$barcode .= chr(hexdec('1D')).'k'.chr(69).chr(strlen("$text")).$text;
				break;
			case 'C128A':
				$barcode .= chr(hexdec('1D')).'k'.chr(73).chr(strlen("$text")).$text;
				break;
			case 'C128B':
				$barcode .= chr(hexdec('1D')).'k'.chr(73).chr(strlen("$text")).chr(hexdec('7B')).chr(hexdec('42')).$text;
				break;
			case 'C128C':
				$barcode .= chr(hexdec('1D')).'k'.chr(73).chr(strlen("$text")).chr(hexdec('7B')).chr(hexdec('43')).$text;
				break;
		}
		return $barcode;
	}

	/**
	 * Center text in a fixed width for the receipt printer.
	 *
	 * @param string $text Text to center.
	 * @param int $width Width in number of characters.
	 * @return string The centered text.
	 * @access private
	 */
	private function receipt_format_center($text, $width) {
		$text_width = strlen($text);
		if ($text_width >= $width -1)
			return $text;
		$pad_front = floor(($width - $text_width) / 2);
		$pad_back = $width - $text_width - $pad_front;
		return str_repeat(' ', $pad_front).$text.str_repeat(' ', $pad_back);
	}

	/**
	 * Generate receipt code for the receipt printer from the sale.
	 *
	 * @param int $width Width in number of characters for font 1.
	 * @param int $width2 Width in number of characters for font 2.
	 * @return string The receipt code.
	 */
	public function receipt_text($width, $width2) {
		global $pines;
		$lines = array();
		// Gather all the receipt data.
		switch ($this->status) {
			case 'quoted':
				$lines = $pines->config->com_sales->quote_receipt_header;
				$name = 'Quote';
				$date = $this->p_cdate;
				break;
			case 'invoiced':
				$lines = $pines->config->com_sales->invoice_receipt_header;
				$name = 'Invoice';
				$date = $this->invoice_date;
				break;
			case 'voided':
				$lines = $pines->config->com_sales->void_receipt_header;
				$name = 'Sale';
				$date = $this->tender_date;
				break;
			case 'paid':
			default:
				$lines = $pines->config->com_sales->receipt_header;
				$name = 'Sale';
				$date = $this->tender_date;
				break;
		}
		$lines = explode("\n", $lines);
		if ($pines->config->com_sales->center_receipt_headers) {
			foreach ($lines as &$cur_line) {
				$cur_line = $this->receipt_format_center($cur_line, $width);
			}
			unset($cur_line);
		}
		$lines[] = '';
		$lines[] = 'Printed '.format_date(time(), 'full_short');
		if (isset($this->user))
			$lines[] = 'Rep: '.$this->user->name;
		if (isset($this->group))
			$lines[] = 'Location: '.$this->group->name;
		if (isset($this->customer))
			$lines[] = 'Customer: '.$this->customer->name;
		$lines[] = '';
		$line = $name.' #'.$this->id;
		$date_string = format_date($date, 'full_short');
		$line .= str_repeat(' ', ($width - strlen($line) - strlen($date_string))).$date_string;
		$lines[] = $line;
		$lines[] = '     * - Non Taxable Items';
		$lines[] = '';
		$total_items = 0;
		foreach ($this->products as $cur_product) {
			$line = sprintf('% -12s', $cur_product['entity']->sku).' x '.sprintf('%4d', $cur_product['quantity']).' @$'.$pines->com_sales->round($cur_product['price'], true);
			$line_total = '$'.$pines->com_sales->round($cur_product['line_total'], true);
			$line .= str_repeat(' ', ($width - strlen($line) - strlen($line_total))).$line_total;
			$line2 = "   {$cur_product['entity']->name}";
			if ($cur_product['entity']->tax_exempt)
				$line2 .= '*';
			if ($cur_product['discount'])
				$line2 .= ' -$'.$pines->com_sales->round($cur_product['discount'], true);
			if ($cur_product['fees'])
				$line2 .= ' +$'.$pines->com_sales->round($cur_product['fees'], true);
			$lines[] = $line;
			$lines[] = $line2;
			if (!empty($cur_product['serial']))
				$lines[] = "   Serial: {$cur_product['serial']}";
			$total_items += $cur_product['quantity'];
		}
		$lines[] = '';
		$lines[] = "Total Items: $total_items";
		if ($this->specials) {
			$lines[] = '';
			$lines[] = 'Specials';
			// Print the before tax note.
			foreach ($this->specials as $cur_special) {
				if ($cur_special['before_tax']) {
					$lines[] = '     * - Before Tax';
					break;
				}
			}
			$lines[] = '';
			foreach ($this->specials as $cur_special) {
				$line = $cur_special['entity']->hide_code ? '' : $cur_special['code'];
				$line_total = '($'.$pines->com_sales->round($cur_special['discount'], true).')';
				$line .= str_repeat(' ', ($width - strlen($line) - strlen($line_total))).$line_total;
				$line2 = "   {$cur_special['name']}";
				if ($cur_special['before_tax'])
					$line2 .= '*';
				$lines[] = $line;
				$lines[] = $line2;
			}
			$lines[] = '';
		}
		$lines[] = sprintf('%'.($width - 10).'s', 'Subtotal:').sprintf('%10s', '$'.$pines->com_sales->round($this->subtotal, true));
		if (!empty($this->total_specials))
			$lines[] = sprintf('%'.($width - 10).'s', 'Specials:').sprintf('%10s', '($'.$pines->com_sales->round($this->total_specials, true).')');
		if (!empty($this->item_fees))
			$lines[] = sprintf('%'.($width - 10).'s', 'Item Fees:').sprintf('%10s', '$'.$pines->com_sales->round($this->item_fees, true));
		$lines[] = sprintf('%'.($width - 10).'s', 'Tax:').sprintf('%10s', '$'.$pines->com_sales->round($this->taxes, true));
		$lines[] = sprintf('%'.($width - 10).'s', 'Total:').sprintf('%10s', '$'.$pines->com_sales->round($this->total, true));
		if ($this->status != 'invoiced' && $this->status != 'quoted') {
			$lines[] = '';
			$lines[] = sprintf('%'.($width - 10).'s', 'Amount Tendered:').sprintf('%10s', '$'.$pines->com_sales->round($this->amount_tendered, true));
			$lines[] = sprintf('%'.($width - 10).'s', 'Change:').sprintf('%10s', '$'.$pines->com_sales->round($this->change, true));
			$lines[] = '';
			$lines[] = 'Payment Via:';
			foreach ($this->payments as $cur_payment) {
				$lines[] = sprintf('%'.($width - 10).'s', $cur_payment['label'].':').sprintf('%10s', '$'.$pines->com_sales->round($cur_payment['amount'], true));
			}
		}
		$lines[] = '';
		// Barcode
		$lines[] = $this->receipt_format_barcode("SA{$this->id}");

		// -- Make the code. --
		// Select standard mode.
		$data = chr(hexdec('1B')).'S';
		// Select the font.
		$data .= chr(hexdec('1B')).'M'.chr(0);
		// Concatenate the receipt data.
		$data .= implode("\n", $lines);
		// Print the receipt label.
		switch ($this->status) {
			case 'quoted':
				$label = $pines->config->com_sales->quote_note_label;
				$text = $pines->config->com_sales->quote_note_text;
				break;
			case 'invoiced':
				$label = $pines->config->com_sales->invoice_note_label;
				$text = $pines->config->com_sales->invoice_note_text;
				break;
			case 'paid':
				$label = $pines->config->com_sales->receipt_note_label;
				$text = $pines->config->com_sales->receipt_note_text;
				break;
			case 'processed':
				$label = $pines->config->com_sales->return_note_label;
				$text = $pines->config->com_sales->return_note_text;
				break;
		}
		if (!empty($text)) {
			$data .= "\n\n";
			// Select the font.
			$data .= chr(hexdec('1B')).'M'.chr(1);
			$data .= "$label\n";
			// Select the font.
			$data .= chr(hexdec('1B')).'M'.chr(2);
			$data .= wordwrap($text, $width2, "\n", true);
			// Reset the font.
			$data .= chr(hexdec('1B')).'M'.chr(0);
		}
		// Feed the paper more.
		$data .= chr(hexdec('1B')).'d'.chr(6);
		// Cut the paper.
		$data .= chr(hexdec('1B')).'m';
		// Buzz
		$data .= chr(hexdec('1B')).chr(hexdec('1E'));
		return $data;
	}

	/**
	 * Remove an item from the sale.
	 * 
	 * After the stock is removed, the sale is saved.
	 * 
	 * @param int $key The key of the product entry.
	 * @param com_sales_stock &$item The stock item to remove.
	 * @return bool True on success, false on failure.
	 */
	public function remove_item($key, &$item) {
		global $pines;
		// Make sure this sale has been invoiced or tendered.
		if ($this->status != 'invoiced' && $this->status != 'paid') {
			// Make sure this sale has not been voided.
			if ($this->status == 'voided') {
				pines_notice('This sale was voided, items cannot be removed.');
				return false;
			} else {
				pines_notice('This sale isn\'t invoiced, items cannot be removed.');
				return false;
			}
		}
		// Make sure this item is not attached to any returns.
		$attached_return = $pines->entity_manager->get_entity(
			array('class' => com_sales_return, 'skip_ac' => true),
			array('&',
				'tag' => array('com_sales', 'return'),
				'ref' => array(
					array('sale', $this),
					array('products', $item)
				)
			)
		);
		if (isset($attached_return)) {
			pines_notice('This item cannot be removed, because it is attached to a return.');
			return false;
		}
		// Make sure the item is correctly located and available.
		if (!isset($item->guid) || isset($item->location->guid) || $item->available) {
			pines_notice('This item cannot be removed, because it is not both out of inventory and unavailable.');
			return false;
		}
		// Verify the item on the sale.
		if (!isset($this->products[$key]) || !is_array($this->products[$key]['stock_entities'])) {
			pines_notice('This item cannot be removed, because the sale reference info is invalid.');
			return false;
		}
		$stock_key = $item->array_search($this->products[$key]['stock_entities']);
		if ($stock_key === false) {
			pines_notice('This item cannot be removed, because it was not found on the sale.');
			return false;
		}
		if ($item->in_array($this->products[$key]['returned_stock_entities'])) {
			pines_notice('This item cannot be removed, because it is marked as returned.');
			return false;
		}
		// Make sure the product can be sold as a warehouse item.
		if ($this->products[$key]['delivery'] != 'warehouse' && $this->products[$key]['entity']->stock_type != 'stock_optional') {
			pines_notice('This item cannot be removed, because the product is not stock optional so cannot be sold as a warehouse order.');
			return false;
		}
		// Make sure we can save the sale.
		if (!$this->save()) {
			pines_notice('This item cannot be removed, because the sale cannot be saved.');
			return false;
		}


		// Put the old item back into inventory.
		$last_tx = $pines->entity_manager->get_entity(
				array('reverse' => true, 'class' => com_sales_tx),
				array('&',
					'tag' => array('com_sales', 'transaction', 'stock_tx'),
					'strict' => array('type', 'removed'),
					'ref' => array(
						array('ref', $this),
						array('stock', $item)
					)
				)
			);
		if ($last_tx) {
			if (!$item->receive('sale_removed', $this, $last_tx->old_location)) {
				pines_notice('Could not receive item ['.$item->guid.'] back into inventory.');
				return false;
			}
			pines_notice("Returned item to its original location, {$last_tx->old_location->name}.");
		} else {
			if (!$item->receive('sale_removed', $this, $this->group)) {
				pines_notice('Could not receive item ['.$item->guid.'] back into inventory.');
				return false;
			}
			pines_notice("Returned item to the sale's location, {$this->group->name}.");
		}
		if (!$item->save()) {
			pines_notice('Could not save item ['.$item->guid.']');
			return false;
		}
		// Make a transaction entry.
		$tx = com_sales_tx::factory('sale_tx', 'swap');
		$tx->type = 'swap_out';
		$tx->ticket = $this;
		$tx->item = $item;
		$tx->save();

		// Update the item on the sale.
		if (isset($this->products[$key]['serial']))
			$this->products[$key]['serial'] = '';
		unset($this->products[$key]['stock_entities'][$stock_key]);
		if ($this->products[$key]['delivery'] != 'warehouse') {
			$this->products[$key]['delivery'] = 'warehouse';
			$this->products[$key]['shipped_entities'] = array();
			// Put all remaining stock into the shipped array.
			foreach ($this->products[$key]['stock_entities'] as $cur_stock) {
				if (!isset($cur_stock->guid) || $cur_stock->in_array($this->products[$key]['returned_stock_entities']))
					continue;
				$this->products[$key]['shipped_entities'][] = $cur_stock;
			}
		}
		$shipped_key = $item->array_search((array) $this->products[$key]['shipped_entities']);
		if ($shipped_key !== false)
			unset($this->products[$key]['shipped_entities'][$shipped_key]);
		$this->warehouse = true;

		// Done, save the sale.
		if (!$this->save()) {
			pines_notice('Could not save the sale after swapping.');
			return false;
		}
		return true;
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
				if ($cur_product['delivery'] == 'shipped')
					$return = $cur_stock->remove('sold_pending_shipping', $this, $cur_stock->location) && $cur_stock->save() && $return;
				elseif ($cur_product['delivery'] == 'pick-up')
					$return = $cur_stock->remove('sold_pending_pickup', $this, $cur_stock->location) && $cur_stock->save() && $return;
				else
					$return = $cur_stock->remove('sold_at_store', $this) && $cur_stock->save() && $return;
			}
		}
		unset($cur_product);
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
	 * Save the sale.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $pines;
		if (!isset($this->status))
			$this->status = 'quoted';
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_sales_sale');

		// Set special warehouse vars.
		if ($this->warehouse) {
			$this->warehouse_pending = $this->warehouse_assigned = $this->warehouse_shipped = false;
			foreach ($this->products as $cur_product) {
				if ($cur_product['delivery'] != 'warehouse')
					continue;
				// Check that all stock entities have been assigned.
				if (count($cur_product['stock_entities']) < ($cur_product['quantity'] + $cur_product['returned_quantity'])) {
					$this->warehouse_pending = true;
					continue;
				}
				// Check where the stock entities are.
				foreach ($cur_product['stock_entities'] as $cur_stock) {
					if ($cur_stock->in_array($cur_product['returned_stock_entities']) || $cur_stock->in_array((array) $cur_product['shipped_entities']))
						continue;
					// It's not with the customer, so not delivered.
					$this->warehouse_assigned = true;
					// There is stock that needs to be shipped.
					$this->add_tag('shipping_pending');
				}
				foreach ((array) $cur_product['shipped_entities'] as $cur_stock_entity) {
					if (!$cur_stock_entity->in_array((array) $cur_product['returned_stock_entities'])) {
						$this->warehouse_shipped = true;
						break;
					}
				}
			}
		}

		// Check all products are shipped.
		if ($this->has_tag('shipping_pending')) {
			$all_shipped = true;
			foreach ($this->products as $cur_product) {
				if (!in_array($cur_product['delivery'], array('shipped', 'warehouse')))
					continue;
				// Calculate included stock entries.
				$stock_entries = $cur_product['stock_entities'];
				$shipped_stock_entries = (array) $cur_product['shipped_entities'];
				foreach ((array) $cur_product['returned_stock_entities'] as $cur_stock_entity) {
					$i = $cur_stock_entity->array_search($stock_entries);
					if (isset($i))
						unset($stock_entries[$i]);
					// If it's still in there, it was entered on the sale twice (fulfilled after returned once), so don't remove it from shipped.
					if (!$cur_stock_entity->in_array($stock_entries)) {
						$i = $cur_stock_entity->array_search($shipped_stock_entries);
						if (isset($i))
							unset($shipped_stock_entries[$i]);
					}
				}
				// If shipped entities is less than quantity, there are still products to ship.
				if (count($shipped_stock_entries) < $cur_product['quantity']) {
					$all_shipped = false;
					break;
				}
			}
			if ($all_shipped) {
				// All shipped, so mark the sale.
				$this->remove_tag('shipping_pending');
				$this->add_tag('shipping_shipped');
			}
		}

		if (empty($this->products)) {
			pines_log("Sale {$this->id} has no products. Cannot be saved.", 'error');
			return false;
		}
		return parent::save();
	}

	/**
	 * Swap an item in the sale.
	 * 
	 * After the swap is performed, the sale is saved.
	 * 
	 * @param int $key The key of the product entry.
	 * @param com_sales_stock &$old_item The old item.
	 * @param com_sales_stock &$new_item The new item.
	 * @return bool True on success, false on failure.
	 */
	public function swap($key, &$old_item, &$new_item) {
		global $pines;
		// Make sure this sale has been invoiced or tendered.
		if ($this->status != 'invoiced' && $this->status != 'paid') {
			// Make sure this sale has not been voided.
			if ($this->status == 'voided') {
				pines_notice('This sale was voided, items cannot be swapped.');
				return false;
			} else {
				pines_notice('This sale isn\'t invoiced, items cannot be swapped.');
				return false;
			}
		}
		// Make sure this item is not attached to any returns.
		$attached_return = $pines->entity_manager->get_entity(
			array('class' => com_sales_return, 'skip_ac' => true),
			array('&',
				'tag' => array('com_sales', 'return'),
				'ref' => array(
					array('sale', $this),
					array('products', $old_item)
				)
			)
		);
		if (isset($attached_return)) {
			pines_notice('This item cannot be swapped out, because it is attached to a return.');
			return false;
		}
		// Make sure the old and new items are correctly located and available.
		if (!isset($old_item->guid) || isset($old_item->location->guid) || $old_item->available) {
			pines_notice('This item cannot be swapped out, because it is not both out of inventory and unavailable.');
			return false;
		}
		if (!isset($new_item->guid) || !isset($new_item->location->guid) || !$new_item->available) {
			pines_notice('The new item cannot be swapped in, because it is not both in inventory and available.');
			return false;
		}
		// Make sure the old and new items are of the same product.
		if (!isset($new_item->product->guid) || !$new_item->product->is($this->products[$key]['entity'])) {
			pines_notice('The new item cannot be swapped in, because it is not the same product as on the sale.');
			return false;
		}
		// Verify the item on the sale.
		if (!isset($this->products[$key]) || !is_array($this->products[$key]['stock_entities'])) {
			pines_notice('This item cannot be swapped, because the sale reference info is invalid.');
			return false;
		}
		$stock_key = $old_item->array_search($this->products[$key]['stock_entities']);
		if ($stock_key === false) {
			pines_notice('This item cannot be swapped, because it was not found on the sale.');
			return false;
		}
		if ($old_item->in_array($this->products[$key]['returned_stock_entities'])) {
			pines_notice('This item cannot be swapped out, because it is marked as returned.');
			return false;
		}
		// Make sure we can save the sale.
		if (!$this->save()) {
			pines_notice('This item cannot be swapped, because the sale cannot be saved.');
			return false;
		}


		// Take the new item out of inventory.
		if (!$new_item->remove('sold_swapped', $this) || !$new_item->save()) {
			pines_notice('Unable to remove item ['.$new_item->guid.'] from inventory');
			return false;
		}
		// Make a transaction entry.
		$tx = com_sales_tx::factory('sale_tx', 'swap');
		$tx->type = 'swap_in';
		$tx->ticket = $this;
		$tx->item = $new_item;
		$tx->save();

		// Put the old item back into inventory.
		$last_tx = $pines->entity_manager->get_entity(
				array('reverse' => true, 'class' => com_sales_tx),
				array('&',
					'tag' => array('com_sales', 'transaction', 'stock_tx'),
					'strict' => array('type', 'removed'),
					'ref' => array(
						array('ref', $this),
						array('stock', $old_item)
					)
				)
			);
		if ($last_tx) {
			if (!$old_item->receive('sale_swapped', $this, $last_tx->old_location)) {
				pines_notice('Could not receive item ['.$old_item->guid.'] back into inventory.');
				return false;
			}
			pines_notice("Returned old item to its original location, {$last_tx->old_location->name}.");
		} else {
			if (!$old_item->receive('sale_swapped', $this, $this->group)) {
				pines_notice('Could not receive item ['.$old_item->guid.'] back into inventory.');
				return false;
			}
			pines_notice("Returned old item to the sale's location, {$this->group->name}.");
		}
		if (!$old_item->save()) {
			pines_notice('Could not save item ['.$old_item->guid.']');
			return false;
		}
		// Make a transaction entry.
		$tx = com_sales_tx::factory('sale_tx', 'swap');
		$tx->type = 'swap_out';
		$tx->ticket = $this;
		$tx->item = $old_item;
		$tx->save();

		// Update the item on the sale.
		if (isset($this->products[$key]['serial']))
			$this->products[$key]['serial'] = $new_item->serial;
		$this->products[$key]['stock_entities'][$stock_key] = $new_item;
		$shipped_key = $old_item->array_search((array) $this->products[$key]['shipped_entities']);
		if ($shipped_key !== false)
			$this->products[$key]['shipped_entities'][$shipped_key] = $new_item;

		// Done, save the sale.
		if (!$this->save()) {
			pines_notice('Could not save the sale after swapping.');
			return false;
		}
		return true;
	}

	/**
	 * Print a form to swap items.
	 *
	 * Uses a page override to only print the form.
	 *
	 * @return module The form's module.
	 */
	public function swap_form() {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_sales', 'forms/swap', 'content');
		$module->entity = $this;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Swap a salesperson on an item in the sale.
	 *
	 * @param int $key The key (index) of the product.
	 * @param user $new_salesrep The new salesperson for the item.
	 * @return bool True on success, false on failure.
	 */
	public function swap_salesrep($key, $new_salesrep = null) {
		//global $pines;
		// Make sure this sale has been invoiced or tendered.
		if ($this->status != 'invoiced' && $this->status != 'paid') {
			// Make sure this sale has not been voided.
			if ($this->status == 'voided') {
				pines_notice('This sale was voided, items cannot be swapped.');
				return false;
			} else {
				pines_notice('This sale isn\'t invoiced, items cannot be swapped.');
				return false;
			}
		}
		/* Not included because only one item may have been returned...
		// Make sure this sale is not attached to any returns.
		$attached_return = $pines->entity_manager->get_entity(
			array('class' => com_sales_return, 'skip_ac' => true),
			array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $this))
		);
		if (isset($attached_return)) {
			pines_notice('This item cannot be swapped, because it is attached to a return.');
			return false;
		}
		*/

		if (!isset($this->products[$key])) {
			pines_notice('This item cannot be swapped, because it is not on the sale.');
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
			// Add a negative amount to offset the positive from the sale.
			$old_salesrep->commissions[] = array(
				'date' => time(),
				'amount' => $this->products[$key]['commission'] * $this->products[$key]['quantity'] * -1,
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
			'amount' => $this->products[$key]['commission'] * $this->products[$key]['quantity'],
			'ticket' => $this,
			'product' => $this->products[$key]['entity'],
			'note' => "Credited/Swapped from {$old_salesrep->name} [{$old_salesrep->username}]."
		);

		return $new_salesrep->save();
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
				if (!isset($this->guid))
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
		$this->amount_tendered = (float) $pines->com_sales->round($amount_tendered);
		$this->amount_due = (float) $pines->com_sales->round($amount_due);
		$this->change = (float) $pines->com_sales->round($change);
		return ($this->save() && $return);
	}

	/**
	 * Count how many of a product are on a sale.
	 * 
	 * @param com_sales_product|int $product The product entity or GUID.
	 * @return int|bool The product count, or false on error.
	 */
	public function product_count($product) {
		$count = 0;
		if (is_object($product))
			$product = $product->guid;
		if (!isset($product))
			return false;
		$product = (int) $product;
		foreach ($this->products as $cur_product) {
			if ($cur_product['entity']->guid === $product)
				$count += $cur_product['quantity'];
		}
		return $count;
	}

	/**
	 * Apply a special to a product/products and get the sum of the discounts.
	 * 
	 * @param string $id A unique ID to keep track of specials applied to a product.
	 * @param string $discount The discount amount to apply.
	 * @param com_sales_product|int $product The product or GUID to apply the discount to.
	 * @param bool $only_one_item Only apply this special to the first item of this product.
	 * @return float The total specials added to products.
	 */
	private function product_special($id, $discount, $product = null, $only_one_item = false) {
		global $pines;
		$total = 0;
		if (isset($product)) {
			if (is_object($product))
				$product = $product->guid;
			if (!isset($product))
				return 0.00;
			$product = (int) $product;
		}
		foreach ($this->products as &$cur_product) {
			if (isset($product) && $cur_product['entity']->guid !== $product)
				continue;
			$cur_price = (float) $cur_product['price'];
			$cur_qty = (int) $cur_product['quantity'];
			$cur_discount = $cur_product['discount'];
			if ($cur_product['entity']->discountable && $cur_discount != "")
				$cur_price = $this->discount_price($cur_price, $cur_discount);
			// Calculate the special price for an individual item.
			// This will calculate the percentage value for a percentage or just the amount for a flat discount.
			$cur_special = (float) $pines->com_sales->round($cur_price - $this->discount_price($cur_price, $discount));
			if ($cur_special <= 0)
				continue;
			// Now calculate the total special for this line.
			if (!$only_one_item)
				$cur_special *= $cur_qty;
			if (!$cur_product['specials'])
				$cur_product['specials'] = array();
			$cur_product['specials'][$id] = $cur_special;
			$total += $cur_special;
			if ($only_one_item)
				break;
		}
		unset($cur_product);
		// Return the total of all specials newly applied to products.
		return $total;
	}

	/**
	 * Remove a special from a product/products.
	 * 
	 * @param string $id A unique ID to keep track of specials applied to a product.
	 */
	private function product_special_remove($id) {
		foreach ($this->products as &$cur_product) {
			if (isset($cur_product['specials'][$id]))
				unset($cur_product['specials'][$id]);
		}
		unset($cur_product);
	}

	/**
	 * Get the total of a product on a sale.
	 * 
	 * Adds the line total of all lines which include the product.
	 * 
	 * @param com_sales_product|int $product The product entity or GUID.
	 * @return int|bool The total, or false on error.
	 */
	public function product_total($product) {
		$total = 0;
		if (is_object($product))
			$product = $product->guid;
		if (!isset($product))
			return false;
		$product = (int) $product;
		foreach ($this->products as $cur_product) {
			if ($cur_product['entity']->guid === $product)
				$total += $cur_product['line_total'];
		}
		return $total;
	}

	/**
	 * Calculate and set the sale's totals.
	 *
	 * This process adds "line_total" and "fees" to each product on the sale,
	 * and adds "specials", "subtotal", "total_specials", "item_fees", "taxes",
	 * and "total" to the sale itself.
	 * 
	 * -- Important Note --
	 * This function must remain functionally identical to the update_products()
	 * JavaScript function in the sale form view.
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
					'tag' => array('com_sales', 'tax_fee'),
					'data' => array('enabled', true)
				)
			);
		foreach ($tax_fees as $key => $cur_tax_fee) {
			foreach($cur_tax_fee->locations as $cur_location) {
				// If we're in one of its groups, don't remove it.
				if (isset($_SESSION['user']) && $_SESSION['user']->in_group($cur_location))
					continue 2;
			}
			// We're not in any of its groups, so remove it.
			unset($tax_fees[$key]);
		}
		// Once we get eligible specials, don't get them again.
		if (!isset($this->elig_specials)) {
			// Find eligible specials.
			$specials = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_special),
					array('&',
						'tag' => array('com_sales', 'special'),
						'data' => array(
							array('enabled', true),
							array('apply_to_all', true)
						)
					)
				);
			$this->elig_specials = array();
			foreach ($specials as $cur_special) {
				if (!$cur_special->eligible())
					continue;
				$this->elig_specials[] = $cur_special;
			}
		}
		$subtotal = 0.00;
		$this->specials = array();
		$taxes = 0.00;
		$item_fees = 0.00;
		$total = 0.00;
		// How many times to apply a flat tax.
		$tax_qty = 0;
		$taxable_subtotal = 0.00;
		// Go through each product, calculating its line total and fees.
		foreach ($this->products as &$cur_product) {
			$price = (float) $cur_product['price'];
			$qty = (int) $cur_product['quantity'];
			$discount = $cur_product['discount'];
			// Reset the specials array.
			$cur_product['specials'] = array();
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
			$item_fees += $cur_product['fees'];
			$subtotal += $cur_product['line_total'];
		}
		unset($cur_product);
		$this->subtotal = (float) $pines->com_sales->round($subtotal);

		// Now that we know the subtotal, we can use it for specials.
		$total_before_tax_specials = 0.00;
		$total_specials = 0.00;
		// First build an array of specials that match and their discounts.
		foreach (array_merge((array) $this->added_specials, (array) $this->elig_specials) as $cur_special) {
			$apply_special = true;
			foreach ($cur_special->requirements as $cur_req) {
				if (!$apply_special)
					continue;
				switch ($cur_req['type']) {
					case "subtotal_eq":
						if ($pines->com_sales->round($this->subtotal, true) != $pines->com_sales->round($cur_req['value'], true))
							$apply_special = false;
						break;
					case "subtotal_lt":
						if ($pines->com_sales->round($this->subtotal) >= $pines->com_sales->round($cur_req['value']))
							$apply_special = false;
						break;
					case "subtotal_gt":
						if ($pines->com_sales->round($this->subtotal) <= $pines->com_sales->round($cur_req['value']))
							$apply_special = false;
						break;
					case "has_product":
						if (!$this->product_count($cur_req['value']))
							$apply_special = false;
						break;
					case "has_not_product":
						if ($this->product_count($cur_req['value']))
							$apply_special = false;
						break;
				}
			}
			if (!$apply_special)
				continue;
			// The special works, now calculate its value.
			$discount = 0.00;
			// This unique ID is used to keep track of the specials applied to a sale.
			$id = uniqid();
			foreach ($cur_special->discounts as $cur_dis) {
				switch ($cur_dis['type']) {
					case "order_amount":
						$discount += $cur_dis['value'];
						break;
					case "order_percent":
						// Old way. Doesn't save product discount totals.
						//$discount += $this->subtotal * ($cur_dis['value'] / 100);
						// New way.
						$discount += $this->product_special($id, "{$cur_dis['value']}%");
						break;
					case "product_amount":
						// Old way. Doesn't save product discount totals.
						//$qty = $this->product_count($cur_dis['qualifier']);
						//$discount += $qty * $cur_dis['value'];
						// New way.
						$discount += $this->product_special($id, "\${$cur_dis['value']}", $cur_dis['qualifier']);
						break;
					case "product_percent":
						// Old way. Doesn't save product discount totals.
						//$prod_total = $this->product_total($cur_dis['qualifier']);
						//$discount += $prod_total * ($cur_dis['value'] / 100);
						// New way.
						$discount += $this->product_special($id, "{$cur_dis['value']}%", $cur_dis['qualifier']);
						break;
					case "item_amount":
						// Old way. Doesn't save product discount totals.
						//if (!$this->product_count($cur_dis['qualifier']))
						//	break;
						//$discount += $cur_dis['value'];
						// New way.
						$discount += $this->product_special($id, "\${$cur_dis['value']}", $cur_dis['qualifier'], true);
						break;
					case "item_percent":
						// Old way. Doesn't save product discount totals.
						//if (!$this->product_count($cur_dis['qualifier']))
						//	break;
						//$found_product = null;
						//foreach ($this->products as $cur_product) {
						//	if ($cur_product['entity']->is($cur_dis['qualifier'])) {
						//		$found_product = $cur_product;
						//		break;
						//	}
						//}
						//if (!$found_product)
						//	break;
						//$prod_price = (float) $found_product['price'];
						//$discount += $prod_price * ($cur_dis['value'] / 100);
						// New way.
						$discount += $this->product_special($id, "{$cur_dis['value']}%", $cur_dis['qualifier'], true);
						break;
				}
			}
			$discount = (float) $pines->com_sales->round($discount);
			if ($discount <= 0) {
				// Remove the special amounts saved on products.
				$this->product_special_remove($id);
				continue;
			}
			// Check a number restriction.
			if ($cur_special->per_ticket != 0) {
				$matching = 0;
				foreach ($this->specials as $cur_test) {
					if ($cur_special->is($cur_test['entity']))
						$matching++;
				}
				if ($matching >= $cur_special->per_ticket) {
					// Remove the special amounts saved on products.
					$this->product_special_remove($id);
					continue;
				}
			}
			$this->specials[] = array(
				"entity" => $cur_special,
				"id" => $id,
				"code" => $cur_special->code,
				"name" => $cur_special->name,
				"before_tax" => $cur_special->before_tax,
				"discount" => $discount
			);
		}
		// Now remove specials with other special requirements.
		foreach ($this->specials as $key => $cur_special) {
			$apply_special = true;
			foreach ($cur_special['entity']->requirements as $cur_req) {
				if (!$apply_special)
					continue;
				switch ($cur_req['type']) {
					case "has_special":
						$matching = 0;
						if ($cur_req['value'] === 'any') {
							// Have to subtract this special.
							$matching = count($this->specials) - 1;
						} else {
							foreach ($this->specials as $cur_test) {
								if ($cur_test['entity']->is($cur_req['value']))
									$matching++;
							}
						}
						if (!$matching)
							$apply_special = false;
						break;
					case "has_not_special":
						$matching = 0;
						if ($cur_req['value'] === 'any') {
							// Have to subtract this special.
							$matching = count($this->specials) - 1;
						} else {
							foreach ($this->specials as $cur_test) {
								if ($cur_test['entity']->is($cur_req['value']))
									$matching++;
							}
						}
						if ($matching)
							$apply_special = false;
						break;
				}
			}
			if (!$apply_special) {
				// Remove the special amounts saved on products.
				$this->product_special_remove($cur_special['id']);
				unset($this->specials[$key]);
				continue;
			}
			// Add up the total discounts.
			if ($cur_special['before_tax'])
				$total_before_tax_specials += $cur_special['discount'];
			$total_specials += $cur_special['discount'];
		}
		// Now total the specials for each product.
		foreach ($this->products as &$cur_product) {
			$cur_product['specials_total'] = 0.00;
			foreach ((array) $cur_product['specials'] as $cur_special_amount)
				$cur_product['specials_total'] += $cur_special_amount;
		}
		unset($cur_product);
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
		// The total can now be calculated.
		$total = ($this->subtotal - $this->total_specials) + $this->item_fees + $this->taxes;
		$this->total = (float) $total;
		return true;
	}

	/**
	 * Void the sale.
	 *
	 * A sale transaction is created, and the sale's status is changed to
	 * 'voided'.
	 *
	 * @param bool $force Whether to ignore errors during payment voiding.
	 * @return bool True on success, false on failure.
	 */
	public function void($force = false) {
		global $pines;
		if ($this->status == 'voided')
			return true;
		// Check if this sale is attached to any returns. If so, it cannot be voided.
		$attached_return = $pines->entity_manager->get_entity(
				array('class' => com_sales_return, 'skip_ac' => true),
				array('&',
					'tag' => array('com_sales', 'return'),
					'ref' => array('sale', $this)
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
				// Copy the stock array so we can manipulate it by reference.
				$stock_entities = $cur_product['stock_entities'];
				foreach ($stock_entities as &$cur_stock) {
					if (!isset($cur_stock->guid))
						continue;
					$last_tx = $pines->entity_manager->get_entity(
							array('reverse' => true, 'class' => com_sales_tx),
							array('&',
								'tag' => array('com_sales', 'transaction', 'stock_tx'),
								'strict' => array('type', 'removed'),
								'ref' => array('ref', $this)
							)
						);
					if ($last_tx) {
						$return = $cur_stock->receive('sale_voided', $this, $last_tx->old_location) && $return;
						$return = $cur_stock->save() && $return;
					} else {
						$return = $cur_stock->receive('sale_voided', $this) && $return;
						$return = $cur_stock->save() && $return;
					}
				}
				unset($cur_stock);
				$cur_product['stock_entities'] = $stock_entities;
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
						'fees' => $cur_product['fees'],
						'salesperson' => $cur_product['salesperson']
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
						'fees' => $cur_product['fees'],
						'salesperson' => $cur_product['salesperson']
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
					if ($force)
						pines_error('There was an error while voiding the payment '.$cur_payment['label'].'. It is being ignored because the void is being forced.');
					else
						$return = false;
				} else {
					// Make a transaction entry.
					$tx = com_sales_tx::factory('payment_tx');
					$tx->type = 'payment_voided';
					$tx->amount = (float) $cur_payment['amount'];
					$tx->ref = $cur_payment['entity'];

					// Make sure we have a GUID before saving the tx.
					if (!isset($this->guid))
						$return = $this->save() && $return;

					$tx->ticket = $this;
					$return = $tx->save() && $return;
				}
			}
			unset($cur_payment);
		}
		if ($this->added_commission) {
			$users = array();
			foreach ($this->products as $cur_product) {
				if (!isset($cur_product['salesperson']))
					continue;
				if (!$cur_product['salesperson']->in_array($users) && is_array($cur_product['salesperson']->commissions))
					$users[] = $cur_product['salesperson'];
			}
			foreach ($users as $cur_user) {
				foreach ($cur_user->commissions as &$cur_commission) {
					if ($this->is($cur_commission['ticket'])) {
						$cur_commission['note'] = "Sale was voided. Amount: \${$cur_commission['amount']}";
						$cur_commission['amount'] = 0;
					}
				}
				unset($cur_commission);
				$cur_user->save();
			}
		}

		// Complete the transaction.
		if ($return) {
			// Make a transaction entry.
			$tx = com_sales_tx::factory('sale_tx');

			$this->status = 'voided';
			$tx->type = 'voided';

			// Make sure we have a GUID before saving the tx.
			if (!isset($this->guid))
				$return = $this->save() && $return;

			$tx->ticket = $this;
			$return = $tx->save() && $return;
		}

		$this->void_date = time();
		return ($this->save() && $return);
	}
}

?>