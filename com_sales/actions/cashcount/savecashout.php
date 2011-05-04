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

if ( !gatekeeper('com_sales/editcashcount') )
	punt_user(null, pines_url('com_sales', 'cashcount/list'));
$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!isset($cashcount->guid)) {
	pines_error('Requested cash count id is not accessible.');
	return;
}
if ($cashcount->cashed_out) {
	pines_notice('This cash count has already been cashed-out.');
	return;
}

$cashcount->cashed_out = true;
$cashcount->cashed_out_date = time();
pines_session();
$cashcount->cashed_out_user = $_SESSION['user'];
$cashcount->comments = $_REQUEST['comments'];
$cashcount->total_out = 0;
// Save the total count of each different denomination.
foreach ($cashcount->currency as $key => $cur_currency) {
	// The float is the total amount of money in the drawer to begin with.
	$cashcount->count_out[$key] = (int) $_REQUEST["count_$key"];
	$cashcount->total_out += ((float) $cur_currency) * $cashcount->count_out[$key];
}

if ($pines->config->com_sales->global_cashcounts)
	$cashcount->ac->other = 1;

if ($cashcount->save()) {
	pines_notice('Cash Count ['.$cashcount->guid.'] Cashed-Out with $'.$cashcount->total_out.'.');
} else {
	$cashcount->cash_out();
	pines_error('Error saving Cash Count. Do you have permission?');
	return;
}

redirect(pines_url('com_sales', 'cashcount/list'));

?>