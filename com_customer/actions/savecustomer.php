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
	$customer = com_customer_customer::factory((int) $_REQUEST['id']);
	if (is_null($customer->guid)) {
		display_error('Requested customer id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_customer/newcustomer') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_customer', 'listcustomers', null, false));
		return;
	}
	$customer = com_customer_customer::factory();
}

if (!isset($customer))
	$customer = (object) array();

// General
$customer->name_first = $config->run_sales->title_case($_REQUEST['name_first']);
$customer->name_last = $config->run_sales->title_case($_REQUEST['name_last']);
$customer->name = "{$customer->name_first} {$customer->name_last}";
if ($config->com_customer->ssn_field)
	$customer->ssn = $_REQUEST['ssn'];
$customer->email = $_REQUEST['email'];
$customer->company = $_REQUEST['company'];
$customer->job_title = $_REQUEST['job_title'];
$customer->address_type = $_REQUEST['address_type'];
$customer->address_1 = $_REQUEST['address_1'];
$customer->address_2 = $_REQUEST['address_2'];
$customer->city = $_REQUEST['city'];
$customer->state = $_REQUEST['state'];
$customer->zip = $_REQUEST['zip'];
$customer->address_international = $_REQUEST['address_international'];
$customer->phone_cell = $_REQUEST['phone_cell'];
$customer->phone_work = $_REQUEST['phone_work'];
$customer->phone_home = $_REQUEST['phone_home'];
$customer->fax = $_REQUEST['fax'];
$customer->login_disabled = ($_REQUEST['login_disabled'] == 'ON');
if (!empty($_REQUEST['password']))
	$customer->password = $_REQUEST['password'];
$customer->description = $_REQUEST['description'];
$customer->short_description = $_REQUEST['short_description'];

// Points
if ($config->com_customer->adjustpoints && gatekeeper('com_customer/adjustpoints'))
	$customer->adjust_points((int) $_REQUEST['adjust_points']);

// Addresses
$customer->addresses = json_decode($_REQUEST['addresses']);

// Attributes
$customer->attributes = json_decode($_REQUEST['attributes']);

if (empty($customer->name)) {
	$customer->print_form();
	display_notice('Please specify a name.');
	return;
}
if (empty($customer->email)) {
	$customer->print_form();
	display_notice('Please specify an email.');
	return;
}
if (empty($customer->phone_cell) && empty($customer->phone_work) && empty($customer->phone_home)) {
	$customer->print_form();
	display_notice('Please specify at least one phone number.');
	return;
}

if ($config->com_customer->global_customers) {
	$customer->ac = (object) array('other' => 1);
}

if ($customer->save()) {
	display_notice('Saved customer ['.$customer->name.']');
} else {
	display_error('Error saving customer. Do you have permission?');
}

$config->run_customer->list_customers();
?>