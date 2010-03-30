<?php
/**
 * Login a user and return a JSON result.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customertimer/login') ||  !gatekeeper('com_customertimer/loginpwless') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'status', null, false));

$pines->page->override = true;

$return = false;

if (isset($_REQUEST['id'])) {
	$customer = com_customer_customer::factory((int) $_REQUEST['id']);
	$station = $_REQUEST['station'];
	if (is_null($customer->guid)) {
		display_notice('Customer ID not found.');
		$return = false;
	} else {
		$logins = com_customertimer_login_tracker::factory();
		$return = $logins->login($customer, $station);
	}
}

$pines->page->override_doc(json_encode($return));

?>