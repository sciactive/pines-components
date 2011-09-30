<?php
/**
 * Payment step of checkout.
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
if (!$pines->com_storefront->build_sale())
	return;

// Load the steps module.
$pines->com_storefront->checkout_step('3');

// Load the review module if the pages are combined.
if ($pines->config->com_storefront->review_in_payment_page) {
	$module = new module('com_storefront', 'checkout/review', 'content');
	$module->entity = $_SESSION['com_storefront_sale'];
	$module->no_form = true;
}

$module = new module('com_storefront', 'checkout/payment', 'content');
$module->payment_types = (array) $pines->entity_manager->get_entities(
		array('class' => com_sales_payment_type, 'skip_ac' => true),
		array('&',
			'tag' => array('com_sales', 'payment_type'),
			'data' => array('storefront', true)
		)
	);

// Show the extra review controls if the pages are combined.
$module->review_form = $pines->config->com_storefront->review_in_payment_page;

if (empty($_SESSION['com_storefront_sale']->payments))
	$module->payment = (object) array();
else
	$module->payment = (object) $_SESSION['com_storefront_sale']->payments[0];

?>