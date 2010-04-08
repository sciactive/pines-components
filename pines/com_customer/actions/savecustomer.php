<?php
/**
 * Save changes to a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_customer/editcustomer') )
		punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'listcustomers'));
	$customer = com_customer_customer::factory((int) $_REQUEST['id']);
	if (is_null($customer->guid)) {
		pines_error('Requested customer id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_customer/newcustomer') )
		punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'listcustomers'));
	$customer = com_customer_customer::factory();
}

// General
$customer->name_first = $pines->com_customer->title_case($_REQUEST['name_first']);
$customer->name_middle = $pines->com_customer->title_case($_REQUEST['name_middle']);
$customer->name_last = $pines->com_customer->title_case($_REQUEST['name_last']);
$customer->name = "{$customer->name_first} {$customer->name_last}";
if ($pines->config->com_customer->ssn_field)
	$customer->ssn = preg_replace('/\D/', '', $_REQUEST['ssn']);
$customer->dob = strtotime($_REQUEST['dob']);
$customer->email = $_REQUEST['email'];
$customer->company = null;
if (preg_match('/^\d+/', $_REQUEST['company'])) {
	$customer->company = com_customer_company::factory(intval($_REQUEST['company']));
	if (is_null($customer->company->guid))
		$customer->company = null;
}
$customer->job_title = $_REQUEST['job_title'];
$customer->phone_cell = preg_replace('/\D/', '', $_REQUEST['phone_cell']);
$customer->phone_work = preg_replace('/\D/', '', $_REQUEST['phone_work']);
$customer->phone_home = preg_replace('/\D/', '', $_REQUEST['phone_home']);
$customer->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
$customer->login_disabled = ($_REQUEST['login_disabled'] == 'ON');
// Temporarily save the password in case the customer doesn't pass all checks.
if (!empty($_REQUEST['password']))
	$customer->tmp_password = $_REQUEST['password'];
$customer->description = $_REQUEST['description'];

// Account
if ($_REQUEST['member'] == 'ON') {
	$customer->make_member();
} else {
	$customer->member = false;
}
$customer->member_exp = strtotime($_REQUEST['member_exp']);
if ($pines->config->com_customer->adjustpoints && gatekeeper('com_customer/adjustpoints'))
	$customer->adjust_points((int) $_REQUEST['adjust_points']);

// Addresses
$customer->address_type = $_REQUEST['address_type'];
$customer->address_1 = $_REQUEST['address_1'];
$customer->address_2 = $_REQUEST['address_2'];
$customer->city = $_REQUEST['city'];
$customer->state = $_REQUEST['state'];
$customer->zip = $_REQUEST['zip'];
$customer->address_international = $_REQUEST['address_international'];
$customer->addresses = (array) json_decode($_REQUEST['addresses']);
foreach ($customer->addresses as &$cur_address) {
	$array = array(
		'type' => $cur_address->values[0],
		'address_1' => $cur_address->values[1],
		'address_2' => $cur_address->values[2],
		'city' => $cur_address->values[3],
		'state' => $cur_address->values[4],
		'zip' => $cur_address->values[5]
	);
	$cur_address = $array;
}
unset($cur_address);

// Attributes
$customer->attributes = (array) json_decode($_REQUEST['attributes']);
foreach ($customer->attributes as &$cur_attribute) {
	$array = array(
		'name' => $cur_attribute->values[0],
		'value' => $cur_attribute->values[1]
	);
	$cur_attribute = $array;
}
unset($cur_attribute);

// Customer validation to ensure that the fields were filled out correctly.
if (in_array('name', $pines->config->com_customer->required_fields_customer) && empty($customer->name)) {
	$customer->print_form();
	pines_notice('Please specify a name.');
	return;
}
if (empty($customer->ssn)) {
	if (in_array('ssn', $pines->config->com_customer->required_fields_customer)) {
		$customer->print_form();
		pines_notice('Please provide an SSN.');
		return;
	}
} else {
	if (!preg_match('/\d{9}/', $customer->ssn)) {
		$customer->print_form();
		pines_notice('The SSN must be a 9 digit number.');
		return;
	}
	$test = $pines->entity_manager->get_entity(array('data' => array('ssn' => $customer->ssn), 'tags' => array('com_customer', 'customer'), 'class' => com_customer_customer));
	if (isset($test) && !$customer->is($test)) {
		$customer->print_form();
		pines_notice("The customer {$test->name} already has this SSN.");
		return;
	}
}
if (in_array('dob', $pines->config->com_customer->required_fields_customer) && empty($customer->dob)) {
	$customer->print_form();
	pines_notice('Please specify a date of birth.');
	return;
}
if (in_array('email', $pines->config->com_customer->required_fields_customer) && empty($customer->email)) {
	$customer->print_form();
	pines_notice('Please specify an email.');
	return;
}
if (in_array('company', $pines->config->com_customer->required_fields_customer) && empty($customer->company)) {
	$customer->print_form();
	pines_notice('Please specify a company.');
	return;
}
if (in_array('phone', $pines->config->com_customer->required_fields_customer) &&
	empty($customer->phone_cell) &&
	empty($customer->phone_work) &&
	empty($customer->phone_home)) {
	$customer->print_form();
	pines_notice('Please specify at least one phone number.');
	return;
}
if (in_array('password', $pines->config->com_customer->required_fields_customer) && empty($customer->tmp_password)) {
	$customer->print_form();
	pines_notice('Please specify a password.');
	return;
}
if (in_array('description', $pines->config->com_customer->required_fields_customer) && empty($customer->description)) {
	$customer->print_form();
	pines_notice('Please specify a description.');
	return;
}
if ( in_array('address', $pines->config->com_customer->required_fields_customer) ) {
	switch ($customer->address_type) {
			case 'us':
				if (empty($customer->address_1) ||
					empty($customer->city) ||
					empty($customer->state)) {
					$customer->print_form();
					pines_notice('Please specify a complete address.');
					return;
				}
				break;
			case 'international':
				if (empty($customer->address_international)) {
					$customer->print_form();
					pines_notice('Please specify an address.');
					return;
				}
				break;
	}
}
// If the customer data passes all validation, use the temp password.
if (!empty($customer->tmp_password)) {
	$customer->password = $customer->tmp_password;
	unset($customer->tmp_password);
}
if ($pines->config->com_customer->global_customers)
	$customer->ac->other = 1;

if ($customer->save()) {
	pines_notice('Saved customer ['.$customer->name.']');
} else {
	pines_error('Error saving customer. Do you have permission?');
}

$pines->com_customer->list_customers();
?>
