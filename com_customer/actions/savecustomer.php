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
	$customer = $config->run_customer->get_customer($_REQUEST['id']);
	if (is_null($customer)) {
		display_error('Requested customer id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_customer/newcustomer') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_customer', 'listcustomers', null, false));
		return;
	}
	$customer = new entity('com_customer', 'customer');
}

// General
$customer->name = $_REQUEST['name'];
$customer->enabled = ($_REQUEST['enabled'] == 'ON' ? true : false);
$customer->description = $_REQUEST['description'];
$customer->short_description = $_REQUEST['short_description'];

// Attributes
$customer->attributes = json_decode($_REQUEST['attributes']);

if (empty($customer->name)) {
	$module = $config->run_customer->print_customer_form('com_customer', 'savecustomer');
	$module->entity = $customer;
	display_error('Please specify a name.');
	return;
}
$test = $config->entity_manager->get_entities_by_data(array('name' => $customer->name), array('com_customer', 'customer'));
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
	$module = $config->run_customer->print_customer_form('com_customer', 'savecustomer');
	$module->entity = $customer;
	display_error('There is already a customer with that name. Please choose a different name.');
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