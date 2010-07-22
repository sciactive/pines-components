<?php
/**
 * com_sales' configuration defaults.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'com_customer',
		'cname' => 'CRM Integration',
		'description' => 'Integrate with com_customer.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'com_hrm',
		'cname' => 'HRM Integration',
		'description' => 'Integrate with com_hrm.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'remove_stock',
		'cname' => 'Remove Stock',
		'description' => 'When (in the sale process) to take stock out of inventory.',
		'value' => 'tender',
		'options' => array(
			'When the sale is invoiced.' => 'invoice',
			'When the sale is tendered.' => 'tender'
		),
		'peruser' => true,
	),
	array(
		'name' => 'perform_actions',
		'cname' => 'Perform Product Actions',
		'description' => 'When (in the sale process) to perform any product actions defined on the products being sold.',
		'value' => 'tender',
		'options' => array(
			'When the sale is invoiced.' => 'invoice',
			'When the sale is tendered.' => 'tender'
		),
		'peruser' => true,
	),
	array(
		'name' => 'add_commission',
		'cname' => 'Add Commission',
		'description' => 'When (in the sale process) to calculate and add commission to the employee. This requires HRM integration.',
		'value' => 'tender',
		'options' => array(
			'When the sale is invoiced.' => 'invoice',
			'When the sale is tendered.' => 'tender'
		),
		'peruser' => true,
	),
	array(
		'name' => 'autocomplete_product',
		'cname' => 'Autocomplete Product',
		'description' => 'Use a product autocomplete selector on sales and returns.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'currency_symbol',
		'cname' => 'Currency Symbol',
		'description' => 'The currency symbol to use. (E.g. $, ¥, €, Rp)',
		'value' => '$',
	),
	array(
		'name' => 'currency_denominations',
		'cname' => 'Currency Denominations',
		'description' => 'The currency denominations to use. (E.g. 0.01, 0.05, 0.10, 0.25, 1, 5, 10, 20, 50, 100)',
		'value' => array('0.01', '0.05', '0.10', '0.25', '1', '5', '10', '20', '50', '100'),
	),
	array(
		'name' => 'dec',
		'cname' => 'Visible Decimal Places',
		'description' => 'Decimal numbers, though stored in the database more accurately, will only be displayed to this number of places.',
		'value' => 2,
		'peruser' => true,
	),
	array(
		'name' => 'center_receipt_headers',
		'cname' => 'Center Receipt Headers',
		'description' => 'Center headers for the receipt printer versions of receipts.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'quote_receipt_header',
		'cname' => 'Quote Receipt Header',
		'description' => 'This header will appear on the receipt printer version of the receipt.',
		'value' => "PINES\n123 Street\nCity, ST 00000\nwww.example.com\n\nQuote",
		'peruser' => true,
	),
	array(
		'name' => 'quote_note_label',
		'cname' => 'Quote Note Label',
		'description' => 'The quote note will be appended to all quotes.',
		'value' => 'Return Policy:',
		'peruser' => true,
	),
	array(
		'name' => 'quote_note_text',
		'cname' => 'Quote Note Text',
		'description' => 'The quote note will be appended to all quotes.',
		'value' => 'You (Buyer) have 14 (fourteen) calendar days from the date on your Sales Invoice to Return the item(s) purchased. All returns will be subject to a 15% restocking fee made payable at the time of return. All restocking fees must be in the form of credit card or money order. All returns must be in original condition including item purchased, packaging, accessories, software, cords or other items. We reserve the right to request identification and to deny any return.',
		'peruser' => true,
	),
	array(
		'name' => 'invoice_receipt_header',
		'cname' => 'Invoice Receipt Header',
		'description' => 'This header will appear on the receipt printer version of the receipt.',
		'value' => "PINES\n123 Street\nCity, ST 00000\nwww.example.com\n\nInvoice",
		'peruser' => true,
	),
	array(
		'name' => 'invoice_note_label',
		'cname' => 'Invoice Note Label',
		'description' => 'The invoice note will be appended to all invoices.',
		'value' => 'Return Policy:',
		'peruser' => true,
	),
	array(
		'name' => 'invoice_note_text',
		'cname' => 'Invoice Note Text',
		'description' => 'The invoice note will be appended to all invoices.',
		'value' => 'You (Buyer) have 14 (fourteen) calendar days from the date on your Sales Invoice to Return the item(s) purchased. All returns will be subject to a 15% restocking fee made payable at the time of return. All restocking fees must be in the form of credit card or money order. All returns must be in original condition including item purchased, packaging, accessories, software, cords or other items. We reserve the right to request identification and to deny any return.',
		'peruser' => true,
	),
	array(
		'name' => 'receipt_header',
		'cname' => 'Receipt Receipt Header',
		'description' => 'This header will appear on the receipt printer version of the receipt.',
		'value' => "PINES\n123 Street\nCity, ST 00000\nwww.example.com\n\nSale Receipt",
		'peruser' => true,
	),
	array(
		'name' => 'receipt_note_label',
		'cname' => 'Receipt Note Label',
		'description' => 'The receipt note will be appended to all receipts.',
		'value' => 'Return Policy:',
		'peruser' => true,
	),
	array(
		'name' => 'receipt_note_text',
		'cname' => 'Receipt Note Text',
		'description' => 'The receipt note will be appended to all receipts.',
		'value' => 'You (Buyer) have 14 (fourteen) calendar days from the date on your Sales Invoice to Return the item(s) purchased. All returns will be subject to a 15% restocking fee made payable at the time of return. All restocking fees must be in the form of credit card or money order. All returns must be in original condition including item purchased, packaging, accessories, software, cords or other items. We reserve the right to request identification and to deny any return.',
		'peruser' => true,
	),
	array(
		'name' => 'return_receipt_header',
		'cname' => 'Return Receipt Header',
		'description' => 'This header will appear on the receipt printer version of the receipt.',
		'value' => "PINES\n123 Street\nCity, ST 00000\nwww.example.com\n\nReturn Receipt",
		'peruser' => true,
	),
	array(
		'name' => 'return_note_label',
		'cname' => 'Receipt Note Label',
		'description' => 'The receipt note will be appended to all return receipts.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'return_note_text',
		'cname' => 'Receipt Note Text',
		'description' => 'The receipt note will be appended to all return receipts.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'void_receipt_header',
		'cname' => 'Void Receipt Header',
		'description' => 'This header will appear on the receipt printer version of the receipt.',
		'value' => "PINES\n123 Street\nCity, ST 00000\nwww.example.com\n\nVoid Sale",
		'peruser' => true,
	),
	array(
		'name' => 'email_receipt',
		'cname' => 'Email Receipt',
		'description' => 'Email a copy of the receipt to the customer when the sale is tendered.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'email_from_address',
		'cname' => 'From Address',
		'description' => 'The address the email will be sent from.',
		'value' => 'sales@sciactive.com',
		'peruser' => true,
	),
	array(
		'name' => 'receipt_printer',
		'cname' => 'Receipt Printer',
		'description' => 'Integrate the POS with a receipt printer. (Requires specific settings and a special script.)',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'auto_receipt_printer',
		'cname' => 'Auto Print Receipt',
		'description' => 'Print the receipt to a receipt printer automatically when the receipt is shown after quoting/invoicing/completing a sale/return.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'cash_drawer',
		'cname' => 'Cash Drawer',
		'description' => 'Integrate the POS with a cash drawer. (Requires the Pines Cash Drawer Firefox addon.)',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'decline_countsheets',
		'cname' => 'Decline Countsheets',
		'description' => 'Automatically decline countsheets if they are missing any inventory when they are committed.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'global_sales',
		'cname' => 'Globalize Sales',
		'description' => 'Ensure that every user can access all sales by setting the "other" access control to read.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'global_returns',
		'cname' => 'Globalize Returns',
		'description' => 'Ensure that every user can access all returns by setting the "other" access control to read.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'global_countsheets',
		'cname' => 'Globalize Countsheets',
		'description' => 'Ensure that every user can access all countsheets by setting the "other" access control to read.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'global_products',
		'cname' => 'Globalize Products',
		'description' => 'Ensure that every user can access all products by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'global_manufacturers',
		'cname' => 'Globalize Manufacturers',
		'description' => 'Ensure that every user can access all manufacturers by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'global_vendors',
		'cname' => 'Globalize Vendors',
		'description' => 'Ensure that every user can access all vendors by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'global_shippers',
		'cname' => 'Globalize Shippers',
		'description' => 'Ensure that every user can access all shippers by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'global_payment_types',
		'cname' => 'Globalize Payment Types',
		'description' => 'Ensure that every user can access all payment types by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'global_tax_fees',
		'cname' => 'Globalize Taxes/Fees',
		'description' => 'Ensure that every user can access all taxes and fees by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
);

?>