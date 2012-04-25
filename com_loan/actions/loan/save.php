<?php
/**
 * Save or Verify changes to a loan.
 *
 * @package Components
 * @subpackage loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_loan/editloan') )
		punt_user(null, pines_url('com_loan', 'loan/list'));
	$loan = com_loan_loan::factory((int) $_REQUEST['id']);
	if (!isset($loan->guid)) {
		pines_error('Requested loan id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_loan/newloan') )
		punt_user(null, pines_url('com_loan', 'loan/list'));
	$loan = com_loan_loan::factory();
}

// Get customer.
$loan->customer = com_customer_customer::factory((int) $_REQUEST['customer']);
if (!isset($loan->customer->guid))
	$loan->customer = null;

// Get user input variables.
$loan->creation_date = strtotime($_REQUEST['creation_date']);
$loan->principal = $_REQUEST['principal'];
$loan->apr = $_REQUEST['apr'];
$loan->apr_correct = ($_REQUEST['apr_correct'] == 'ON');
$loan->term = $_REQUEST['term']; // Could be in years or months.
$loan->term_type = $_REQUEST['term_type']; // Determines years or months for term.
$loan->first_payment_date = strtotime($_REQUEST['first_payment_date']);
$loan->payment_frequency = $_REQUEST['payment_frequency']; // per year.
$loan->compound_frequency = $_REQUEST['compound_frequency'];
$loan->payment_type = $_REQUEST['payment_type'];

// Check loan process type.
if($_REQUEST['loan_process_type'] == "go_back"){
	$loan->print_form();
	pines_notice('Make necessary changes to loan, then verify the loan again.');
	return;
}

// Check user input for empty values. Required for formulas to work below.
if (!isset($loan->customer->guid)) {
	$loan->print_form();
	pines_notice('Please specify a customer.');
	return;
}
if(!$loan->creation_date){
	$loan->print_form();
	pines_notice('A creation date is required.');
	return;
}
if(empty($loan->principal)){
	$loan->print_form();
	pines_notice('A principal loan amount is required.');
	return;
}
if (preg_match ('/\$/', $loan->principal)) {
	$loan->principal = str_replace('$', '', $loan->principal);
}
if(!is_numeric($loan->principal)){
	$loan->print_form();
	pines_notice('Please enter a numeric value for the principal amount.');
	return;
}
if(empty($loan->apr)){
	$loan->print_form();
	pines_notice('An annual percentage rate (APR) is required.');
	return;
}
if (preg_match ('/\%/', $loan->apr)) {
	$loan->apr = str_replace('%', '', $loan->apr);
}
if(!is_numeric($loan->apr)){
	$loan->print_form();
	pines_notice('Please enter a numeric value for the APR.');
	return;
}
if(!$loan->apr_correct){
	$loan->print_form();
	pines_notice('Please verify that the APR is correct.');
	return;
}
if(empty($loan->term)){
	$loan->print_form();
	pines_notice('A term for the loan is required.');
	return;
}
if(empty($loan->term_type)){
	$loan->print_form();
	pines_notice('A term type for the loan is required.');
	return;
}
if(!$loan->first_payment_date) {
	$loan->print_form();
	pines_notice('A date for the first payment is required.');
	return;
}
// Check payment frequency, also checks for empty value.
switch ($loan->payment_frequency) {
	case "12":
	case "1":
	case "2":
	case "4":
	case "6":
	case "24":
	case "26":
	case "52":
		$loan->payment_frequency = (int) $loan->payment_frequency;
		break;
	default:
		$loan->print_form();
		pines_notice('A payment frequency is required.');
		return;
}
// Check compound frequency, also checks for empty value.
switch ($loan->compound_frequency) {
	case "12":
	case "1":
	case "2":
	case "4":
	case "6":
	case "24":
	case "26":
	case "52":
	case "360":
		$loan->compound_frequency = (int) $loan->compound_frequency;
		break;
	default:
		$loan->print_form();
		pines_notice('A compound frequency is required.');
		return;
}
// Check payment type, also checks for empty value.
switch ($loan->payment_type) {
	case "ending":
		$loan->payment_type = 0;
		break;
	case "beginning":
		$loan->payment_type = 1;
		break;
	default:
		$loan->print_form();
		pines_notice('A payment type is required.');
		return;
}

try {
	$loan->calculate_loan();
} catch (com_loan_loan_terms_not_possible_exception $e) {
	if (empty($_REQUEST['loan_process_type'])) {
		$loan->print_form();
		pines_notice('The terms of this loan were not possible. Please make changes.');
		return;
	}
}

if (empty($_REQUEST['loan_process_type'])){
	// If process type is empty, the loan has not been verified.
	$loan->verify_loan();
	pines_notice('Please verify this loan.');
} elseif ($_REQUEST['loan_process_type'] == "submit"){
	// If process type is submit, save the loan and return to the loan list view.
	// Run the get payments array function to update the grid with possible past due values.
	$loan->get_payments_array();
	if ($loan->save())
		pines_notice('Saved loan '.$loan->id.' for customer '.$loan->customer->name.'.');
	else
		pines_error('Error saving loan. Do you have permission?');
	pines_redirect(pines_url('com_loan', 'loan/list'));
}
?>