<?php
/**
 * Save changes to an audit for a cash count.
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
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcashcounts'));

$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!isset($cashcount->guid)) {
	pines_error('Requested Cash Count id is not accessible.');
	return;
}

$cashcount->update_total();

$audit = com_sales_cashcount_audit::factory();
$audit->creator = $_SESSION['user'];
$audit->count = $_REQUEST['count'];
$audit->comments = $_REQUEST['comments'];
$audit->total = $total_count = 0;
// Save the total count of each different denomination.
foreach ($audit->count as $cur_count) {
	$total_count++;
	$audit->total += $cur_count * $cashcount->currency[$total_count];
}
// The difference between the amount counted to what is in the drawer.
$audit->variance = $audit->total - $cashcount->total;

if ($pines->config->com_sales->global_cashcounts)
	$audit->ac->other = 1;

if ($audit->save()) {
	// Attach this audit to the cashcount it belongs to.
	$cashcount->audits[] = $audit;
	if (!$cashcount->save()) {
		$audit->print_form();
		pines_error('Error saving Cash Count. Do you have permission?');
		return;
	}
	pines_notice('Completed Audit ['.$audit->guid.']');
	if (isset($_SESSION['user']->group->com_sales_task_cashcount_audit)) {
		unset($_SESSION['user']->group->com_sales_task_cashcount_audit);
		$_SESSION['user']->group->save();
	}
} else {
	$audit->cashcount = $cashcount;
	$audit->print_form();
	pines_error('Error saving Audit. Do you have permission?');
	return;
}

redirect(pines_url('com_sales', 'listcashcounts'));

?>