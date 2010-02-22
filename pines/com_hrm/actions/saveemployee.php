<?php
/**
 * Save changes to an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_hrm/editemployee') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'listemployees', null, false));
	$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
	if (is_null($employee->guid)) {
		display_error('Requested employee id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_hrm/newemployee') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'listemployees', null, false));
	$employee = com_hrm_employee::factory();
}

// General
$employee->name_first = $pines->com_hrm->title_case($_REQUEST['name_first']);
$employee->name_middle = $pines->com_hrm->title_case($_REQUEST['name_middle']);
$employee->name_last = $pines->com_hrm->title_case($_REQUEST['name_last']);
$employee->name = "{$employee->name_first} {$employee->name_last}";
if ($pines->config->com_hrm->ssn_field)
	$employee->ssn = preg_replace('/\D/', '', $_REQUEST['ssn']);
$employee->email = $_REQUEST['email'];
$employee->job_title = $_REQUEST['job_title'];
$employee->phone_cell = preg_replace('/\D/', '', $_REQUEST['phone_cell']);
$employee->phone_work = preg_replace('/\D/', '', $_REQUEST['phone_work']);
$employee->phone_home = preg_replace('/\D/', '', $_REQUEST['phone_home']);
$employee->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
$employee->description = $_REQUEST['description'];
$employee->color = $_REQUEST['color'];

// User Account
if (empty($_REQUEST['username']) || !$pines->config->com_hrm->allow_attach) {
	$employee->user_account = null;
} else {
	$employee->user_account = user::factory(preg_match('/^\d+$/', $_REQUEST['username']) ? (int) $_REQUEST['username'] : $_REQUEST['username']);
}

// Addresses
$employee->address_type = $_REQUEST['address_type'];
$employee->address_1 = $_REQUEST['address_1'];
$employee->address_2 = $_REQUEST['address_2'];
$employee->city = $_REQUEST['city'];
$employee->state = $_REQUEST['state'];
$employee->zip = $_REQUEST['zip'];
$employee->address_international = $_REQUEST['address_international'];
$employee->addresses = json_decode($_REQUEST['addresses']);
if (!is_array($employee->addresses))
	$employee->addresses = array();
foreach ($employee->addresses as &$cur_address) {
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
$employee->attributes = json_decode($_REQUEST['attributes']);
if (!is_array($employee->attributes))
	$employee->attributes = array();
foreach ($employee->attributes as &$cur_attribute) {
	$array = array(
		'name' => $cur_attribute->values[0],
		'value' => $cur_attribute->values[1]
	);
	$cur_attribute = $array;
}
unset($cur_attribute);

if (empty($employee->name)) {
	$employee->print_form();
	display_notice('Please specify a name.');
	return;
}
if (empty($employee->email)) {
	$employee->print_form();
	display_notice('Please specify an email.');
	return;
}
if (empty($employee->phone_cell) && empty($employee->phone_work) && empty($employee->phone_home)) {
	$employee->print_form();
	display_notice('Please specify at least one phone number.');
	return;
}
if (gatekeeper('com_hrm/requiressn') && empty($employee->ssn)) {
	$employee->print_form();
	display_notice('Please provide an SSN.');
	return;
}
if (!is_null($employee->user_account) && is_null($employee->user_account->guid)) {
	$employee->print_form();
	display_notice('The user account specified is not accessible.');
	return;
}
$test = $pines->entity_manager->get_entity(array('ref' => array('user_account' => $employee->user_account), 'tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));
if (isset($test) && !$employee->is($test)) {
	$employee->print_form();
	display_notice("The user account specified is already attached to {$test->name}.");
	return;
}

if ($pines->config->com_hrm->global_employees)
	$employee->ac->other = 1;

if ($employee->save()) {
	display_notice('Saved employee ['.$employee->name.']');
} else {
	display_error('Error saving employee. Do you have permission?');
}

$pines->com_hrm->list_employees();
?>