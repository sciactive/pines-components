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
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_sales', 'sale');
	}

	/**
	 * Invoice the sale.
	 *
	 * This process will remove any sold items from stock. Payment is not
	 * considered.
	 *
	 * @return bool True on success, false on any failure.
	 */
	public function invoice() {
		// TODO: Save fees, calculate total.
		if (!is_array($this->products))
			return false;
		// Keep track of the whole process.
		$return = true;
		// Go through each product, marking their stock as sold.
		foreach ($this->products as &$cur_product) {
			if (is_array($cur_product['stock_entities'])) {
				foreach ($cur_product['stock_entities'] as &$cur_stock) {
					if ($cur_product['delivery'] == 'in-store') {
						$return = $return && $cur_stock->remove($this, 'sold_at_store');
					} else {
						$return = $return && $cur_stock->remove($this, 'sold_pending', $cur_stock->location);
					}
				}
			}
		}
		// Make a transaction entry.
		$tx = new entity('com_sales', 'transaction', 'sale_tx');

		if ($this->status)
			$old_status = $this->status;
		$this->status = 'invoiced';
		$tx->type = 'invoiced';

		// Make sure we have a GUID before saving the tx.
		if (!($this->guid))
			$return = $return && $this->save();
		
		$tx->ticket = $this;
		$return = $return && $tx->save();

		return $return;
	}

	public function total() {
		// Here, totals will be calculated.
		if (!is_array($this->products))
			return false;
		// Keep track of the whole process.
		$return = true;
		// Go through each product, calculating its line total and fees.
		foreach ($this->products as &$cur_product) {
			$price = $cur_product['price'];
			$qty = $cur_product['quantity'];
			$discount = $cur_product['discount'];
		}
	}
}

?>