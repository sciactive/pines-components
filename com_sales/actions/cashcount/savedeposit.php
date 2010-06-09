<?php
/**
 * Save changes to a deposit for a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');


if ( !gatekeeper('com_sales/auditcashcount') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'cashcount/list'));

$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!isset($cashcount->guid)) {
	pines_error('Requested Cash Count id is not accessible.');
	return;
}
$cashcount->update_total();

$total_count = 0;
$expected_count = array();
$actual_count = array();
// Total up the skims to find out the expected deposit amount.
foreach ($cashcount->skims as $cur_skim) {
	if ($cur_skim->status == 'pending') {
		foreach ($cur_skim->count as $cur_skim_count)
			$expected_count[] += $cur_skim_count;
	}
}

$deposit = com_sales_cashcount_deposit::factory();
$deposit->creator = $_SESSION['user'];
$deposit->count = $_REQUEST['count'];
$deposit->comments = $_REQUEST['comments'];
$deposit->variance = $total_count = 0;
// Total the actual count of each different denomination.
foreach ($deposit->count as $cur_count) {
	$deposit->variance += $cur_count * $cashcount->currency[$total_count];
	$actual_count[] = $cur_count;
	$total_count++;
}
$deposit->total = $cashcount->total;
// If no skimmed cash is waiting and no cash was counted, the deposit is fine.
if (count($expected_count) == 0 && max($actual_count) == 0)
	$expected_count = $actual_count;

// Validate that the actual deposit amount matches the expected deposit amount.
if ($actual_count != $expected_count) {
	pines_error('This deposit does not match up with all previous skims from the cash drawer.');
	$deposit->status = 'flagged';
} else {
	$deposit->status = 'validated';
}

if ($pines->config->com_sales->global_cashcounts)
	$deposit->ac->other = 1;

if ($deposit->save()) {
	// Attach this deposit to the cashcount it belongs to.
	$cashcount->deposits[] = $deposit;
	if (!$cashcount->save()) {
		$deposit->print_form();
		pines_error('Error saving Cash Count. Do you have permission?');
		return;
	}
	if ($deposit->status == 'validated') {
		// Mark all of the skims as deposited.
		foreach ($cashcount->skims as $cur_skim) {
			$cur_skim->status = 'deposited';
			$cur_skim->save();
		}
		pines_notice('Completed Deposit ['.$deposit->guid.']');
		if (isset($_SESSION['user']->group->com_sales_task_cashcount_deposit)) {
			unset($_SESSION['user']->group->com_sales_task_cashcount_deposit);
			$_SESSION['user']->group->save();
		}
	}
} else {
	$deposit->cashcount = $cashcount;
	$deposit->print_form();
	pines_error('Error saving Deposit. Do you have permission?');
	return;
}

redirect(pines_url('com_sales', 'cashcount/list'));

?>