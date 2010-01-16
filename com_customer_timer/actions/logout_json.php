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

if ( !gatekeeper('com_customer_timer/forcelogout') )
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_customer_timer', 'forcelogout', null, false));

$page->override = true;

$return = false;

if (isset($_REQUEST['id'])) {
	$customer = com_customer_customer::factory((int) $_REQUEST['id']);
	if (is_null($customer->guid)) {
		display_notice('Customer ID not found.');
		$return = false;
	} else {
		$logins = com_customer_timer_login_tracker::factory();
		$return = $logins->logout($customer);
	}
}

$page->override_doc(json_encode($return));

?>