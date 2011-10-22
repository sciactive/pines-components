<?php
/**
 * Load the steps module on the login page during checkout.
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

if (gatekeeper())
	return;

/**
 * Check for the login page to attach the steps module.
 *
 * @param array &$array An array of arguments.
 */
function com_storefront__catch_login_page(&$array) {
	if ($array[0] == 'content' && $array[1] == pines_url('com_storefront', 'checkout/shipping')) {
		global $pines;
		// Load the steps module.
		$pines->com_storefront->checkout_step('1');
	}
}

$pines->hook->add_callback('$pines->user_manager->print_login', -10, 'com_storefront__catch_login_page');

?>