<?php
/**
 * Save payment information.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper()) {
	pines_redirect(pines_url('com_storefront', 'checkout/login'));
	return;
}

if ($pines->config->com_storefront->catalog_mode)
	return;

// Load the sale.
if (!$pines->com_storefront->build_sale()) {
	pines_error('Couldn\'t load sale.');
	return;
}

$data = array();
foreach ($_POST as $key => $value) {
	if ($key == 'com_storefront_payment_id')
		$payment_type = com_sales_payment_type::factory((int) $value);
	else
		$data[$key] = $value;
}

pines_session('write');
$_SESSION['com_storefront_sale']->payments = array(array(
	'entity' => $payment_type,
	'type' => $payment_type->name,
	'amount' => $_SESSION['com_storefront_sale']->total,
	'status' => 'pending',
	'data' => $data
));

// Save the comments if the review page is combined.
if ($pines->config->com_storefront->review_in_payment_page)
	$_SESSION['com_storefront_sale']->comments = $_REQUEST['comments'];

if (!isset($payment_type->guid)) {
	pines_session('close');
	pines_notice('Please select a payment method.');
	pines_action('com_storefront', 'checkout/payment');
	return;
}

if (!$_SESSION['com_storefront_sale']->approve_payments()) {
	pines_session('close');
	pines_notice('Your payment was not approved. Please check all your information.');
	pines_action('com_storefront', 'checkout/payment');
	return;
}
pines_session('close');

if ($pines->config->com_storefront->review_in_payment_page)
	pines_action('com_storefront', 'checkout/reviewsave');
else
	pines_redirect(pines_url('com_storefront', 'checkout/review'));

?>