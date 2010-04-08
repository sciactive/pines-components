<?php
/**
 * Save changes to a company.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_customer/editcompany') )
		punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'listcompanies'));
	$company = com_customer_company::factory((int) $_REQUEST['id']);
	if (is_null($company->guid)) {
		pines_error('Requested company id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_customer/newcompany') )
		punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'listcompanies'));
	$company = com_customer_company::factory();
}

// General
$company->name = $_REQUEST['name'];
$company->email = $_REQUEST['email'];
$company->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
$company->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
$company->website = $_REQUEST['website'];

// Address
$company->address_type = $_REQUEST['address_type'];
$company->address_1 = $_REQUEST['address_1'];
$company->address_2 = $_REQUEST['address_2'];
$company->address_international = $_REQUEST['address_international'];
$company->city = $_REQUEST['city'];
$company->state = $_REQUEST['state'];
$company->zip = $_REQUEST['zip'];

if (empty($company->name)) {
	$company->print_form();
	pines_notice('Please specify a company name.');
	return;
}
if (empty($company->email)) {
	$company->print_form();
	pines_notice('Please specify an email address.');
	return;
}
if (empty($company->phone)) {
	$company->print_form();
	pines_notice('Please specify a phone number.');
	return;
}
if ($company->address_type == 'us' && (empty($company->address_1) || empty($company->city) || empty($company->state) || empty($company->zip))) {
	$company->print_form();
	pines_notice('Please specify an address.');
	return;
}
if ($company->address_type == 'international' && empty($company->address_international)) {
	$company->print_form();
	pines_notice('Please specify an address.');
	return;
}
if ($pines->config->com_customer->global_customers)
	$company->ac->other = 1;

if ($company->save()) {
	pines_notice('Saved company ['.$company->name.']');
} else {
	pines_error('Error saving company. Do you have permission?');
}

$pines->com_customer->list_companies();
?>