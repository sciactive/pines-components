<?php
/**
 * Logout a user and return a JSON result.
 *
 * @package Pines
 * @subpackage com_customer_timer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer_timer/forcelogout') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_customer_timer', 'forcelogout', null, false));
	return;
}

$page->override = true;

$return = false;

if (isset($_REQUEST['id'])) {
	$id = (int) $_REQUEST['id'];
	$customer = com_sales_customer::factory($id);
	if (is_null($customer)) {
		display_notice('Customer ID not found.');
		$return = false;
	} else {
		$logins = $config->run_customer_timer->get_login_entity();
		if (in_array($customer, $logins->customers)) {
			$return = $config->run_customer_timer->logout($customer, $logins);
		}
	}
}

$page->override_doc(json_encode($return));

?>