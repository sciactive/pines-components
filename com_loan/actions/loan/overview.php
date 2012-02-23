<?php
/**
 * Overview a loan and its amortization schedule.
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

if ( !gatekeeper('com_loan/viewloan') )
		punt_user(null, pines_url('com_loan', 'loan/list'));
if ( isset($_REQUEST['id']) ) {
	$loan = com_loan_loan::factory((int) $_REQUEST['id']);
	if (!isset($loan->guid)) {
		pines_error('Requested loan id is not accessible.');
		return;
	}
}

// This overview is different than the amortization from the original creation of the loan.
// It needs to factor in payments that have been made on the loan.

// Create a payments array if payments have been made/missed.
$loan->get_payments_array();
//var_dump($loan->payments);
//var_dump($loan->paid);
$loan->print_overview();
$loan->save();
?>