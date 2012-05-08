<?php
/**
 * Restores deleted payments from a restore point.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_loan/restorepayments') )
		punt_user(null, pines_url('com_loan', 'loan/restorepayments', array('id' => $_REQUEST['id'])));
if ( isset($_REQUEST['id']) ) {
	$loan = com_loan_loan::factory((int) $_REQUEST['id']);
	if (!isset($loan->guid)) {
		pines_error('Requested loan id is not accessible.');
		return;
	}
}
if (!empty($loan->paid) || !empty($loan->history->edit_payments)) {
	// The paid array exists and/or edit-delete history exists
	// ..so create a backup of currently existing data.

	$restore_point_value = $_REQUEST['delete_restore_point'];
	// Get variables
	if (!isset($restore_point_value))
		$restore_point_value = $_REQUEST['restore_point'];
	$restore_name = $_REQUEST['restore_name'][$restore_point_value];

	// Check if entity exists.
	if (!isset($loan->guid)) {
		pines_notice('The given ID could not be found.');
		pines_redirect(pines_url('com_loan', 'loan/list'));
		return;
	}
	// Create all payments history if it doesn't exist yet.
	if (!$loan->history->all_payments) {
		$loan->history->all_payments = array();
	}
	foreach ($loan->history->all_payments as $all_pay) {
		// Find Auto-saves.
		if (preg_match('/^Auto-save/', $all_pay['all_delete']['delete_name'])) {
			$count += 1;
		}
	}
	$new_auto_save = $count + 1;

	// Create delete info
	$delete_info = array(
		'delete_date' => strtotime('now'),
		'delete_name' => 'Auto-save ID: '.$new_auto_save,
		'delete_reason' => 'Replaced by '.$restore_name,
		'delete_user' => $_SESSION['user']->username,
		'delete_guid' => $_SESSION['user_id'],
		'delete_remaining_balance' => $loan->payments[0]['remaining_balance'],
	);
	// Create the record, using a temp array.
	$delete_all = array(
		'pay_by_date' => $loan->pay_by_date,
		'all_payments' => $loan->payments,
		'all_paid' => $loan->paid,
		'all_edit_payment_history' => $loan->history->edit_payments,
		'all_delete' => $delete_info,
	);
	$loan->history->all_payments[] = $delete_all;

	// Now unset everything and save it.
	$loan->paid = null;
	$loan->history->edit_payments = null;

	// Then continue below...
}

// Get variables
if (!isset($restore_point_value))
	$restore_point_value = $_REQUEST['restore_point'];

if (empty($restore_point_value))
	$restore_point_value = $_REQUEST['delete_restore_point'];

$restore_name = $_REQUEST['restore_name'][$restore_point_value];

// Check that the restore point is an integer.
if (!preg_match('/^[0-9]*$/', $restore_point_value)) {
	pines_notice('Please select a valid restore point.');
	pines_redirect(pines_url('com_loan', 'loan/editpayments'));
	return;
}

$count_restores = count($loan->history->all_payments) - 1;

// Check that the restore point is possible.
if ($restore_point_value > $count_restores) {
	// The hidden inputs were tampered with.
	pines_notice('Please select a valid restore point.');
	pines_redirect(pines_url('com_loan', 'loan/editpayments'));
	return;
}

$restore_point = $loan->history->all_payments[$restore_point_value];

// It was necessary to htmlspecialchars the restore_name in the hidden input.
if (htmlspecialchars(strtolower($restore_point['all_delete']['delete_name'])) != strtolower($restore_name)) {
	// The hidden inputs were tampered with.
	pines_notice('Please select a valid restore point.');
	pines_redirect(pines_url('com_loan', 'loan/editpayments'));
	return;
}

// Restore Payments and save them.
$loan->pay_by_date = $restore_point['pay_by_date'];
$loan->paid = $restore_point['all_paid'];
$loan->history->edit_payments = $restore_point['all_edit_payment_history'];
$loan->save();

// Reprocess payments array and save it to update records.
$loan->get_payments_array();
$loan->save();

// Log that we restored payments on this date and then save it.
$restored_history = array();
$restored_history['date_restored'] = strtotime("now");
$restored_history['user'] = $_SESSION['user']->username;
$restored_history['guid'] = $_SESSION['user_id'];
$restored_history['restore_record'] = $restore_point;


if (!$loan->history->restored)
	$loan->history->restored = array();

$loan->history->restored[] = $restored_history;
$loan->save();
//var_dump($loan->payments);
//var_dump($loan->paid);
//exit;
pines_redirect(pines_url('com_loan', 'loan/editpayments', array('id' => $loan->guid)));
?>