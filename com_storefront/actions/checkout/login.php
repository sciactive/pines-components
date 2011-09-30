<?php
/**
 * Begin checkout.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_storefront->catalog_mode)
	return;

if (!gatekeeper()) {
	// Not logged in already.
	// Print a login form.
	$pines->user_manager->print_login('content', pines_url('com_storefront', 'checkout/shipping'));
} else {
	// Logged in.
	pines_redirect(pines_url('com_storefront', 'checkout/shipping'));
}

?>