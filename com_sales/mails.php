<?php
/**
 * com_sales' mails.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'sale_receipt' => array(
		'cname' => 'Sale Receipt',
		'description' => 'A sale receipt email.',
		'view' => 'mails/receipt',
		'has_recipient' => true,
		'unsubscribe' => false,
		'macros' => array(
			'receipt' => 'The receipt content.',
			'sale_id' => 'The sale ID.',
			'sale_total' => 'The sale total.',
		),
	),
	'sale_shipped_tracking' => array(
		'cname' => 'Sale Shipped (Tracking Available)',
		'description' => 'A notification of a warehouse order that has been shipped. Will include tracking URLs.',
		'view' => 'mails/sale_shipped_tracking',
		'has_recipient' => true,
		'unsubscribe' => false,
		'macros' => array(
			'packing_list' => 'The packing list.',
			'sale_id' => 'The sale ID.',
			'sale_total' => 'The sale total.',
			'shipper' => 'The shipper used to ship the order.',
			'tracking_link' => 'The shipment tracking link(s). Separated by line breaks.',
			'eta' => 'The date the shipment is expected to arrive.',
			'address' => 'The shipping address to which the order was shipped.',
			'notes' => 'Any notes on the shipment.',
		),
	),
	'sale_shipped' => array(
		'cname' => 'Sale Shipped (Tracking Unavailable)',
		'description' => 'A notification of a warehouse order that has been shipped. Will NOT include tracking URLs.',
		'view' => 'mails/sale_shipped',
		'has_recipient' => true,
		'unsubscribe' => false,
		'macros' => array(
			'packing_list' => 'The packing list.',
			'sale_id' => 'The sale ID.',
			'sale_total' => 'The sale total.',
			'shipper' => 'The shipper used to ship the order.',
			'eta' => 'The date the shipment is expected to arrive.',
			'address' => 'The shipping address to which the order was shipped.',
			'notes' => 'Any notes on the shipment.',
		),
	),
);

?>