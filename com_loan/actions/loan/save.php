<?php
/**
 * Save or Verify changes to a loan.
 *
 * @package Pines
 * @subpackage com_loan
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
$loan->loan_process_type = $_REQUEST['loan_process_type'];
$loan->creation_date = $_REQUEST['creation_date'];
$loan->principal = $_REQUEST['principal'];
$loan->apr = $_REQUEST['apr'];
$loan->apr_correct = ($_REQUEST['apr_correct'] == 'ON');
$loan->term = $_REQUEST['term']; // Could be in years or months.
$loan->term_type = $_REQUEST['term_type']; // Determines years or months for term.
$loan->first_payment_date = $_REQUEST['first_payment_date'];
$loan->payment_frequency = $_REQUEST['payment_frequency']; // per year.
$loan->compound_frequency = $_REQUEST['compound_frequency'];
$loan->payment_type = $_REQUEST['payment_type'];

// Check loan process type.
if($loan->loan_process_type == "go_back"){
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
if(strtotime($loan->creation_date) == 0){
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
if(strtotime($loan->first_payment_date) == 0){
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

// Calculate rate per period.
$base = (1 + ($loan->apr / $loan->compound_frequency));
$pow = ($loan->compound_frequency / $loan->payment_frequency);
$loan->rate_per_period = (pow($base, $pow)) - 1;


// Calculate basics of amortization schedule.
if($loan->term_type == "years") {
	$nper = $loan->payment_frequency * $loan->term;
} elseif($loan->term_type == "months") {
	$nper = $loan->payment_frequency * (($loan->term) / 12);
}

// Calculate the Frequency Payment
$frequency_payment = -1*($pines->com_financial->PMT(($loan->apr / 100) / $loan->payment_frequency, $nper, $loan->principal, 0.0, $loan->payment_type));
$frequency_payment = round($frequency_payment, 2);
$loan->frequency_payment = $frequency_payment;

// Create payments array for amortization table.
$schedule = array();
$sum_int = 0;
$sum_prin = 0;
$i = 0;
// this loop is only for creating the variables for the amortization table.
for ($i = 0; $i < $nper; $i++) {
	$schedule[$i] = array();
	if ($i == 0) {
		$schedule[$i]['scheduled_date_expected'] = strtotime($loan->first_payment_date);
		$schedule[$i]['scheduled_current_balance'] = $loan->principal;
		$schedule[$i]['payment_interest_expected'] = $pines->com_sales->round(($schedule[$i]['scheduled_current_balance'] * $loan->rate_per_period) / 100, true); 
		$schedule[$i]['payment_principal_expected'] = $frequency_payment - $schedule[$i]['payment_interest_expected'];
		$schedule[$i]['payment_interest_paid'] = 0.00; // no payments made at time of loan creation.
		$schedule[$i]['payment_principal_paid'] = 0.00; // no payments made at time of loan creation.
		$schedule[$i]['payment_amount_paid'] = 0.00; // no payments made at time of loan creation.
		$schedule[$i]['scheduled_balance'] = $schedule[$i]['scheduled_current_balance'] - $schedule[$i]['payment_principal_expected'];
		$schedule[$i]['payment_amount_expected'] = $schedule[$i]['payment_principal_expected'] + $schedule[$i]['payment_interest_expected'];
		$schedule[$i]['next_payment_due_amount'] = $schedule[$i]['payment_interest_expected'] + $schedule[$i]['payment_principal_expected'];
	} else {
		$schedule[$i]['scheduled_date_expected'] = strtotime('+1 month',$schedule[$i-1]['scheduled_date_expected']);
		$schedule[$i]['scheduled_current_balance'] = $schedule[$i - 1]['scheduled_balance'];
		$schedule[$i]['payment_interest_expected'] = $pines->com_sales->round(($schedule[$i]['scheduled_current_balance'] * $loan->rate_per_period) / 100 , true);
		if ($schedule[$i]['scheduled_current_balance'] < $frequency_payment || ($schedule[$i]['scheduled_current_balance'] - $frequency_payment) <= 1) {
			$schedule[$i]['payment_principal_expected'] = $schedule[$i]['scheduled_current_balance'];
		} else {
			$schedule[$i]['payment_principal_expected'] = $frequency_payment - $schedule[$i]['payment_interest_expected'];
		}
		$schedule[$i]['payment_amount_expected'] = $schedule[$i]['payment_principal_expected'] + $schedule[$i]['payment_interest_expected'];
		$schedule[$i]['payment_amount_paid'] = 0.00; // no payments made at time of loan creation. 
		$schedule[$i]['scheduled_balance'] = $schedule[$i]['scheduled_current_balance'] - $schedule[$i]['payment_principal_expected'];
	}
	$schedule[$i]['additional_payment'] = null;
	$schedule[$i]['payment_status'] = "not due yet" ;
	$sum_int = $sum_int + $schedule[$i]['payment_interest_expected']; 
	$sum_prin = $sum_prin + $schedule[$i]['payment_principal_expected']; 
}
$loan->schedule = $schedule;
// Calculate remaining variables.
$loan->number_payments = count($loan->schedule); // needs to happen after payments array.
$loan->total_payment_sum = $sum_int + $sum_prin; //sum of all interests and all principals
$loan->total_interest_sum_original = $sum_int;
$loan->total_interest_sum = $sum_int;
$loan->est_interest_savings = $loan->total_interest_sum_original - $loan->total_interest_sum;

$loan->status = "current";
// If process type is empty, the loan has not been verified.
if(empty($loan->loan_process_type)){
	$loan->verify_loan();
	pines_notice('Please verify this loan.');
	return;
}

// If process type is submit, save the loan and return to the loan list view.
if($loan->loan_process_type == "submit"){
	if ($loan->save()) {
		pines_notice('Saved loan '.$loan->id.' for customer '.$loan->customer->name.'.');
	} else {
		pines_error('Error saving loan. Do you have permission?');
	}
	pines_redirect(pines_url('com_loan', 'loan/list'));
}
?>

