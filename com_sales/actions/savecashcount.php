<?php
/**
 * Save changes to a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editcashcount') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcashcounts'));
	$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
	if (!isset($cashcount->guid)) {
		pines_error('Requested cash count id is not accessible.');
		return;
	}
	if ($cashcount->final) {
		pines_notice('This cash count has already been committed.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newcashcount') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcashcounts'));
	$cashcount = com_sales_cashcount::factory();
}

if (!isset($cashcount->creator))
	$cashcount->creator = $_SESSION['user'];

$cashcount->count = $_REQUEST['count'];
$cashcount->comments = $_REQUEST['comments'];
$cashcount->float = $total_count = 0;
// Save the total count of each different denomination.
foreach ($cashcount->count as $cur_count) {
	$total_count++;
	// The float is the total amount of money in the drawer to begin with.
	$cashcount->float += $cur_count * $cashcount->currency[$total_count];
}

if ($_REQUEST['save'] == 'commit') {
	$cashcount->final = true;
	// Complete the cashcount assignment if one exists for this group.
	if (isset($_SESSION['user']->group->com_sales_task_cashcount)) {
		unset($_SESSION['user']->group->com_sales_task_cashcount);
		$_SESSION['user']->group->save();
	}
}

if ($pines->config->com_sales->global_cashcounts)
	$cashcount->ac->other = 1;

if ($cashcount->save()) {
	if ($cashcount->final) {
		pines_notice('Committed Cash Count ['.$cashcount->guid.']');
	} else {
		pines_notice('Saved Cash Count ['.$cashcount->guid.']');
	}
} else {
	$cashcount->print_form();
	pines_error('Error saving Cash Count. Do you have permission?');
	return;
}

redirect(pines_url('com_sales', 'listcashcounts'));

?>