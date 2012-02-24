<?php
/**
 * Make a payment on a loan.
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

if ( !gatekeeper('com_loan/makepayment') )
		punt_user(null, pines_url('com_loan', 'forms/makepayment'));

if ( isset($_REQUEST['id']) ) {
	$loan = com_loan_loan::factory((int) $_REQUEST['id']);
	if (!isset($loan->guid)) {
		pines_error('Requested loan id is not accessible.');
		return;
	}
}

// Generate form.
// We needed the payments array to be calculated so we could figure out the
// balance we would need to display on the make payment form.

$loan->get_payments_array();

// It's okay if the paid array previously existed, that will give us an
// accurate payments array, which is what we need.
if ($loan->payments[0]['unpaid_balance'] > 0)
	$loan->unpaid_balance = $loan->payments[0]['unpaid_balance'];
else
	$loan->unpaid_balance = 0.00;

if ($loan->payments[0]['unpaid_interest'] > 0)
	$loan->unpaid_interest = $loan->payments[0]['unpaid_interest'];
else
	$loan->unpaid_interest = 0.00;

$past_due = $loan->payments[0]['past_due'] - ($loan->payments[0]['unpaid_balance_not_past_due'] + $loan->payments[0]['unpaid_interest_not_past_due']);
if ($past_due > 0)
	$loan->past_due = $past_due;
else
	$loan->past_due = 0.00;

$num = count($loan->paid) - 1;

if ($loan->paid[$num]['payment_status'] == 'partial_not_due') {
	$loan->past_due = null;
	$loan->balance = $loan->payments[0]['next_payment_due_amount'];
} else
	$loan->balance = $loan->past_due + $loan->payments[0]['next_payment_due_amount'];

$loan->save();
$loan->makepayment_form();
?>