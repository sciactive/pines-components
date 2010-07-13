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
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'cashcount/list'));

$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!isset($cashcount->guid)) {
	pines_error('Requested Cash Count id is not accessible.');
	return;
}
$cashcount->update_total();

$skim = com_sales_cashcount_skim::factory();
// Amount in the drawer.
$skim->till_total = $cashcount->total;
$skim->comments = $_REQUEST['comments'];
$skim->total = 0;
// Save the total count of each different denomination.
foreach ($cashcount->currency as $key => $cur_currency) {
	// The float is the total amount of money in the drawer to begin with.
	$skim->count[$key] = (int) $_REQUEST["count_$key"];
	$skim->total += ((float) $cur_currency) * $skim->count[$key];
}

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

redirect(pines_url('com_sales', 'cashcount/list'));

?>