<?php
/**
 * Complete checkout.
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

// Load the sale if the review page is separate.
if (!$pines->config->com_storefront->review_in_payment_page && !$pines->com_storefront->build_sale()) {
	pines_error('Couldn\'t load sale.');
	return;
}

pines_session('write');
// Save the customer comments.
$_SESSION['com_storefront_sale']->comments = $_REQUEST['comments'];

// Verify all the parts of the sale.
if (
		!isset($_SESSION['com_storefront_sale']->shipping_address->address_type) ||
		(
			$_SESSION['com_storefront_sale']->shipping_address->address_type == 'us' &&
			(
				empty($_SESSION['com_storefront_sale']->shipping_address->address_1) ||
				empty($_SESSION['com_storefront_sale']->shipping_address->city) ||
				empty($_SESSION['com_storefront_sale']->shipping_address->state) ||
				empty($_SESSION['com_storefront_sale']->shipping_address->zip)
			)
		) || (
			$_SESSION['com_storefront_sale']->shipping_address->address_type == 'international' &&
			empty($_SESSION['com_storefront_sale']->shipping_address->address_international)
		)
	) {
	pines_session('close');
	pines_notice('Please provide a shipping address.');
	pines_action('com_storefront', 'checkout/review');
	return;
} elseif (!$_SESSION['com_storefront_sale']->approve_payments()) {
	pines_session('close');
	pines_notice('Your payment was not approved. Please check all your information.');
	pines_action('com_storefront', 'checkout/review');
	return;
}

if (!$_SESSION['com_storefront_sale']->complete()) {
	pines_session('close');
	pines_notice('Your order could not be completed. Please verify your information and submit again.');
	pines_action('com_storefront', 'checkout/review');
	return;
}

$_SESSION['com_storefront_sale']->save();
$_SESSION['com_storefront_sale']->email_receipt();

$url = pines_url('com_storefront', 'checkout/complete', array('id' => $_SESSION['com_storefront_sale']->guid));

unset($_SESSION['com_storefront_sale']);
pines_session('close');
$pines->com_storefront->empty_cart();

// Redirect to a different URL, so they can't resubmit.
pines_redirect($url);

?>