<?php
/**
 * Deletes all payments made on a loan. Saves it in history as a restore point.
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

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_loan/editloan') )
		punt_user(null, pines_url('com_loan', 'loan/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_loan/newloan') )
		punt_user(null, pines_url('com_loan', 'loan/edit'));
}

$loan = com_loan_loan::factory((int) $_REQUEST['id']);

// Arrived here from the editpayments action.
// Check if entity exists.
if (!isset($loan->guid)) {
	pines_notice('The given ID could not be found.');
	pines_redirect(pines_url('com_loan', 'loan/list'));
	return;
}

$delete_all_payments_name = $_REQUEST['delete_all_payments_name'];
$delete_all_payments_reason = $_REQUEST['delete_all_payments_reason'];

$loan->delete_all_payments($delete_all_payments_name, $delete_all_payments_reason);
$loan->save();
// Reprocess and save the payments array so that everything is updated.
$loan->get_payments_array();
$loan->save();
pines_redirect(pines_url('com_loan', 'loan/editpayments', array('id' => $loan->guid)));

?>