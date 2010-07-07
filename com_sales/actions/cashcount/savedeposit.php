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

$deposit = com_sales_cashcount_deposit::factory();
// Amount in the drawer.
$deposit->till_total = $cashcount->total;
$deposit->comments = $_REQUEST['comments'];
$deposit->total = 0;
// Save the total count of each different denomination.
foreach ($cashcount->currency as $cur_currency) {
	$deposit->count[$cur_currency] = (int) $_REQUEST["count_$cur_currency"];
	$deposit->total += ((float) $cur_currency) * $deposit->count[$cur_currency];
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
	pines_notice('Completed Deposit ['.$deposit->guid.']');
	if (isset($_SESSION['user']->group->com_sales_task_cashcount_deposit)) {
		unset($_SESSION['user']->group->com_sales_task_cashcount_deposit);
		$_SESSION['user']->group->save();
	}
} else {
	$deposit->cashcount = $cashcount;
	$deposit->print_form();
	pines_error('Error saving Deposit. Do you have permission?');
	return;
}

redirect(pines_url('com_sales', 'cashcount/list'));

?>