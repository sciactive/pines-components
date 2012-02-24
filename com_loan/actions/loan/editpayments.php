<?php
/**
 * Edit/Delete payments on a loan.
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

if ( !gatekeeper('com_loan/editpayments') )
		punt_user(null, pines_url('com_loan', 'loan/editpayments', array('id' => $_REQUEST['id'], 'payment_amount' => $_REQUEST['payment_amount'], 'payment_date_input' => $_REQUEST['payment_date_input'], 'page' => $_REQUEST['page'])));
if ( isset($_REQUEST['id']) ) {
	$loan = com_loan_loan::factory((int) $_REQUEST['id']);
	if (!isset($loan->guid)) {
		pines_error('Requested loan id is not accessible.');
		return;
	}
}

// If Deleting All Payments:
if ($_REQUEST['editpayments'] == "all_payments") {
	// Deleting all payments is true.
	$delete_all_payments_name = $_REQUEST['delete_all_payments_name'];
	$delete_all_payments_reason = $_REQUEST['delete_all_payments_reason'];
	pines_redirect(pines_url('com_loan', 'loan/deletepayments', array('id' => $loan->guid, 'delete_all_payments_name' => $delete_all_payments_name, 'delete_all_payments_reason' => $delete_all_payments_reason)));
	return;
}



// If Editing or Deleting a Single Payment:
if ($_REQUEST['editpayments'] == "edit_payment") {
	// Get variables from edit payments form.
	// All unedited fields are disabled and would not receive any information.
	$edit_payment_amount = count($_REQUEST['payment_amount']) ? $_REQUEST['payment_amount'] : array();
	$edit_receive_date = count($_REQUEST['receive_date']) ? $_REQUEST['receive_date'] : array();
	$edit_payment_id = count($_REQUEST['payment_id']) ? $_POST['payment_id'] : array();
	$check_delete_payment = count($_REQUEST['delete_payment']) ? $_REQUEST['delete_payment'] : array();
	$edit_error_type = count($_REQUEST['error_type']) ? $_REQUEST['error_type'] : array();
	// So when we create histories of payments, we want the history before ANY of these payments
	// were processed - NOT in between each tweek from editing multiple payments, or it wouldn't make
	// much sense to the user observing the changes.

	// That being the case, get the current, untampered with paid and payments array, and pass them
	// to match_paid_id and match_payment_id instead of looking through them as they change with each
	// payment edit.

	$use_paid_array = $loan->paid;
	$use_payments_array = $loan->payments;

	$c = 0;
	foreach ($edit_payment_amount as $cur_payment_amount) {
		// Define and validate key variables
		$payment_id = $edit_payment_id[$c];
		$payment_amount = $cur_payment_amount;
		// Check the format of the payment amount.
		if (!preg_match('/^\$?[0-9]*\.?[0-9]*$/', $payment_amount)) {
			pines_notice('Please enter only valid payment amounts.');
			pines_redirect(pines_url('com_loan', 'loan/list'));
			return;
		}
		// Remove possible dollar sign from price.
		$payment_amount = str_replace('$', '', $payment_amount);

		// Round payment and past due amounts.
		$payment_amount = $pines->com_sales->round((float)$payment_amount);
		$loan->past_due = $pines->com_sales->round($loan->past_due );

		// Check if deleting the payment
		$delete_this_payment = false;
		foreach ($check_delete_payment as $delete_id) {
			if ($delete_id == $payment_id) {
				// this payment was found in delete_payment array.
				$delete_this_payment = true;
			}
		}


		// Find payment ID in the OLD paid array to see if it exists.
		$loan->match_paid_id($payment_id, $use_paid_array);
		$num = $loan->match_info['num'];
		$parent = $loan->match_info['parent'];
		$loan->match_info = null;

		// Came from edit payments - saving changes to edit payment.
		$date_received = strtotime($edit_receive_date[$c]);
		$error_type = $edit_error_type[$c];

		// Get values for amount of interest, principal, and additional payment.
		// These values get used for two things:
		$date_expected_orig = (int) $use_paid_array[$num]['payment_date_expected'];
		if ($parent) {
			$date_receive_old = $use_paid_array[$num]['payment_date_received'];
			$date_record_old = $use_paid_array[$num]['payment_date_recorded'];
			$payment_interest = $pines->com_sales->round($use_paid_array[$num]['payment_interest_paid']);
			$payment_principal = $pines->com_sales->round($use_paid_array[$num]['payment_principal_paid']);
			$payment_additional = $pines->com_sales->round($use_paid_array[$num]['payment_additional']);
			if ($payment_additional < .01)
				$payment_additional = 0;
		} else {
			foreach ($use_paid_array[$num]['extra_payments'] as $extra_payment) {
				if ($extra_payment['payment_id'] == $payment_id) {
					$date_receive_old = $extra_payment['payment_date_received'];
					$date_record_old = $extra_payment['payment_date_recorded'];
					$payment_interest = $pines->com_sales->round($extra_payment['payment_interest_paid']);
					$payment_principal = $pines->com_sales->round($extra_payment['payment_principal_paid']);
					$payment_additional = $pines->com_sales->round($extra_payment['payment_additional']);
					if ($payment_additional < .01)
						$payment_additional = 0;
					break;
				}
			}
		}

		// Get the corresponding payment from the OLD payment array by ID.
		$loan->match_payment_id($parent, $payment_id, $use_payments_array);
		$store_payment = $loan->store_payment;
		$loan->store_payment = null;

		// This part saves the history of this payment edit/delete.
		$store_paid = $use_paid_array[$num];
		// Save stored payment to history.
		if (!$loan->history->edit_payments)
			$loan->history->edit_payments = array();
		$n = count($loan->history->edit_payments);

		$date_recorded = strtotime('now');
		// This decides if it should process as an editpayment or deletepayment.
		if (!$delete_this_payment) {
			// We are technically deleting this payment, even though we are replacing it
			// with an edit. We should take down some basic info in the delete array.
			$loan->history->edit_payments[$n]['edit_payment'] = $store_payment;
			// Save a stored paid array to history.
			$loan->history->edit_payments[$n]['edit_paid'] = $store_paid;
			$loan->edit_payment($date_received, $date_expected_orig, $date_receive_old, $date_record_old, $date_recorded, $payment_id, $payment_amount, $error_type, $payment_interest, $payment_principal, $payment_additional);
		} elseif ($delete_this_payment) {
			// Delete the payment now. (won't save until end of file)
			$loan->history->edit_payments[$n]['delete_payment'] = $store_payment;
			// Save a stored paid array to history.
			$loan->history->edit_payments[$n]['delete_paid'] = $store_paid;
			$loan->delete_payment($date_received, $date_expected_orig, $date_recorded, $payment_id, $payment_amount, $error_type, $payment_interest, $payment_principal, $payment_additional);
		}
		$c++;
	}

	if (!empty($loan->get_edit_results))
		$loan->get_edit_results($loan->get_edit_results);
	$loan->get_edit_results = null;
	if (!empty($loan->get_delete_results))
		$loan->get_delete_results($loan->get_delete_results);
	$loan->get_delete_results = null;

	// Run clean up pay by dates after making payments.
	$loan->cleanup_pbds();

//	var_dump($loan->pay_by_date);
//	var_dump($loan->paid);
//	var_dump($loan->history->edit_payments);
//	exit;
}

$loan->get_payments_array();
// Save the payments array.
$loan->save();
$loan->print_edit_payments();

?>