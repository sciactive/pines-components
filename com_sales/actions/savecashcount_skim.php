<?php
/**
 * Save changes to a skim off of a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');


if ( !gatekeeper('com_sales/skimcashcount') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcashcounts'));

$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!isset($cashcount->guid)) {
	pines_error('Requested Cash Count id is not accessible.');
	return;
}
$cashcount->update_total();

$skim = com_sales_cashcount_skim::factory();
$skim->creator = $_SESSION['user'];
$skim->count = $_REQUEST['count'];
$skim->comments = $_REQUEST['comments'];
$skim->variance = $total_count = 0;
// Save the total count of each different denomination.
foreach ($skim->count as $cur_count) {
	// The skim variance is what is being taken out of the drawer.
	$skim->variance += $cur_count * $cashcount->currency[$total_count];
	$total_count++;
}
// The skim total is what is still left in the drawer after skimming.
$skim->total = $cashcount->total - $skim->variance;

if ($pines->config->com_sales->global_cashcounts)
	$skim->ac->other = 1;

if ($skim->save()) {
	// Attach this skim to the cashcount it belongs to.
	$cashcount->skims[] = $skim;
	if (!$cashcount->save()) {
		$skim->print_form();
		pines_error('Error saving Cash Count. Do you have permission?');
		return;
	}
	pines_notice('Completed Skim ['.$skim->guid.']');
	if (isset($_SESSION['user']->group->com_sales_task_cashcount_skim)) {
		unset($_SESSION['user']->group->com_sales_task_cashcount_skim);
		$_SESSION['user']->group->save();
	}
} else {
	$skim->cashcount = $cashcount;
	$skim->print_form();
	pines_error('Error saving Skim. Do you have permission?');
	return;
}

redirect(pines_url('com_sales', 'listcashcounts'));

?>