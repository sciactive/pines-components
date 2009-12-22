<?php
/**
 * Save changes to a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_customer/editcustomer') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_customer', 'listcustomers', null, false));
		return;
	}
	$customer = $config->run_sales->get_customer($_REQUEST['id']);
	if (is_null($customer)) {
		display_error('Requested customer id is not accessible');
		return;
	}
} else {
	display_notice('No customer ID given.');
	$config->run_customer->list_customers();
	return;
}

if (!isset($customer->com_customer))
	$customer->com_customer = (object) array();

// General
$customer->com_customer->login_disabled = ($_REQUEST['login_disabled'] == 'ON' ? true : false);
if (!empty($_REQUEST['password']))
	$customer->com_customer->password = $_REQUEST['password'];
$customer->com_customer->description = $_REQUEST['description'];
$customer->com_customer->short_description = $_REQUEST['short_description'];

// Points
if ($config->com_customer->adjustpoints && gatekeeper('com_customer/adjustpoints')) {
	$config->run_customer->adjust_points($customer, (int) $_REQUEST['adjust_points']);
}

// Addresses
$customer->com_customer->addresses = json_decode($_REQUEST['addresses']);

// Attributes
$customer->com_customer->attributes = json_decode($_REQUEST['attributes']);

if ($customer->save()) {
	display_notice('Saved customer ['.$customer->name.']');
} else {
	display_error('Error saving customer. Do you have permission?');
}

$config->run_customer->list_customers();
?>