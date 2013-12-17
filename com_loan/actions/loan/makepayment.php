<?php
/**
 * Makes a payment on a loan.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($_REQUEST['type'] == 'ajax') {
	
	$pines->page->override = true;
	header('Content-Type: application/json');

	if ( !gatekeeper('com_loan/makepayment') ) {
		$result = array('failed' => true);
		$pines->page->override_doc(json_encode($result));
		return;
	}

	if ( isset($_REQUEST['id']) ) {
		$loan = com_loan_loan::factory((int) $_REQUEST['id']);
		if (!isset($loan->guid)) {
			$result = array('no_loan' => true);
			$pines->page->override_doc(json_encode($result));
			return;
		}
	}
} else {
	if ( !gatekeeper('com_loan/makepayment') )
			punt_user(null, pines_url('com_loan', 'loan/makepayment', array('loan_id' => $_REQUEST['loan_id'], 'payment_amount' => $_REQUEST['payment_amount'], 'payment_date_input' => $_REQUEST['payment_date_input'], 'edit' => $_REQUEST['edit'])));
	if ( isset($_REQUEST['loan_id']) ) {
		$loan = com_loan_loan::factory((int) $_REQUEST['loan_id']);
		if (!isset($loan->guid)) {
			pines_error('Requested loan id is not accessible.');
			return;
		}
	}
}

// Process payment.
// Get variables.
$payment_amount = $_REQUEST['payment_amount'];

// Check the format of the payment amount.
if (!preg_match('/^\$?[0-9]*\.?[0-9]*$/', $payment_amount)) {
	if (($_REQUEST['type'] == 'ajax')) {
		$result = array('error' => true);
		$pines->page->override_doc(json_encode($result));
		return;
	} else {
		pines_notice('Please enter a valid payment amount.');
		pines_redirect(pines_url('com_loan', 'loan/list'));
		return;
	}
}

// Remove possible dollar sign from price.
$payment_amount = str_replace('$', '', $payment_amount);


$payment_amount = $pines->com_sales->round((float)$payment_amount);

// Use this to reset the paid array. Make sure not to save at the bottom of this file, if doing that.
//$loan->pay_by_date = null;
//$loan->history->edit_payments = null;
//$loan->history->all_payments = null;
//$loan->history->restored = null;
//$loan->paid = null;
//$loan->save();
//exit;


// Create/Append to paid array.
$remaining_balance = ($loan->payments[0]['remaining_balance']) ? $loan->payments[0]['remaining_balance'] : $loan->principal;
if($remaining_balance < .01) {
	if (($_REQUEST['type'] == 'ajax')) {
		$result = array('paid' => true);
		$pines->page->override_doc(json_encode($result));
		return;
	} else {
		pines_notice('The balance is paid. No more payments accepted.');
		pines_redirect(pines_url('com_loan', 'loan/list'));
		return;
	}
}
$loan->get_payments_array();

// Get paid array variables we will need.
// The date expected variable below will only be used for updating if not using a
// if $loan->pay_by_date array exists, then missed_first_payment won't matter.
if ($loan->payments[1]['payment_status'] != "missed")
	$missed_first_payment = false;
else
	$missed_first_payment = true;

//if ($missed_first_payment == true) {
//	$date_expected = $loan->payments[0]['first_payment_missed'];
//	$missed_first_payment = false;
//} elseif (isset($loan->payments[0]['next_payment_due']))
//	$date_expected = $loan->payments[0]['next_payment_due'];
//else
//	$date_expected = $loan->first_payment_date;
$date_expected = $loan->first_payment_date;
$date_received = strtotime($_REQUEST['payment_date_input']);
$date_recorded = strtotime('now');

// Check for valid date
if (strtotime($date_received) === 0) {
	if (($_REQUEST['type'] == 'ajax')) {
		$result = array('error' => true);
		$pines->page->override_doc(json_encode($result));
		return;
	} else {
		pines_notice('A valid date for receiving payment is required.');
		pines_redirect(pines_url('com_loan', 'loan/list'));
		return;
	}
}

// Generate a random ID number for payment.
$payment_id = uniqid();

// Singular Payment By Date record.
$pbd = array(
	'date_received' => $date_received,
	'date_recorded' => $date_recorded,
	'date_created' => $date_recorded,
	'payment_amount' => $payment_amount,
	'payment_id' => $payment_id,
);

// Insert new $pay_by_date record.
if ($loan->pay_by_date != null) {
	// Insert the PBD into the proper place in the PBD array.
	$loan->insert_pbd($pbd);
	// Now make the payments again using the new PBD array.
	$loan->run_make_payments();
} else {
	$loan->pay_by_date = array();
	$loan->pay_by_date[] = $pbd;
	// Since this is the first and only payment, we can run the make payment function here.
	$loan->make_payment($payment_amount,$date_expected, $date_received, $date_recorded, $payment_id);
}

// Run clean up pay by dates after making payments.
$loan->cleanup_pbds();

//var_dump($missed_first_payment);
//var_dump($loan->pay_by_date);
//var_dump($loan->paid);
//var_dump($loan->payments);
//exit;

// Save the payments array.
if ($loan->save()) {
	if ($_REQUEST['type'] == 'ajax') {
		$result = array('success' => true);
	}
} else {
	if ($_REQUEST['type'] == 'ajax')
		$result = array('failed' => false);
	else
		pines_notice('Loan Payment was not saved.');
}

if ($_REQUEST['type'] == 'ajax') {
	$pines->page->override_doc(json_encode($result));
	return;
}

// Redirect to overview.
if ($_REQUEST['edit'])
	pines_redirect(pines_url('com_loan', 'loan/editpayments', array('id' => $loan->guid)));
else
	pines_redirect(pines_url('com_loan', 'loan/overview', array('id' => $loan->guid)));

?>