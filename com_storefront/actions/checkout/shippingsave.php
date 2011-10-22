<?php
/**
 * Save shipping information.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper()) {
	pines_redirect(pines_url('com_storefront', 'checkout/login'));
	return;
}

if ($pines->config->com_storefront->catalog_mode)
	return;

// Load the sale.
if (!$pines->com_storefront->build_sale())
	return;

pines_session('write');
$_SESSION['com_storefront_sale']->shipping_use_customer = false;
$_SESSION['com_storefront_sale']->shipping_address = (object) array(
	'name' => $_REQUEST['name'],
	'address_type' => $_REQUEST['address_type'] == 'international' ? 'international' : 'us',
	'address_1' => $_REQUEST['address_1'],
	'address_2' => $_REQUEST['address_2'],
	'city' => $_REQUEST['city'],
	'state' => $_REQUEST['state'],
	'zip' => $_REQUEST['zip'],
	'address_international' => $_REQUEST['address_international']
);

if (
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
	pines_notice('Please provide a full address.');
	pines_action('com_storefront', 'checkout/shipping');
	return;
}
pines_session('close');

pines_redirect(pines_url('com_storefront', 'checkout/payment'));

?>