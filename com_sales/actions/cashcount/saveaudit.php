<?php
/**
 * Save changes to an audit for a cash count.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');


if ( !gatekeeper('com_sales/auditcashcount') )
	punt_user(null, pines_url('com_sales', 'cashcount/list'));

$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!isset($cashcount->guid)) {
	pines_error('Requested Cash Count id is not accessible.');
	return;
}

$cashcount->update_total();

$audit = com_sales_cashcount_audit::factory();
// Amount in the drawer.
$audit->till_total = $cashcount->total;
$audit->comments = $_REQUEST['comments'];
$audit->total = 0;
// Save the total count of each different denomination.
foreach ($cashcount->currency as $key => $cur_currency) {
	$audit->count[$key] = (int) $_REQUEST["count_$key"];
	$audit->total += ((float) $cur_currency) * $audit->count[$key];
}

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
		pines_session('write');
		unset($_SESSION['user']->group->com_sales_task_cashcount_audit);
		$_SESSION['user']->group->save();
		pines_session('close');
	}
} else {
	$audit->cashcount = $cashcount;
	$audit->print_form();
	pines_error('Error saving Audit. Do you have permission?');
	return;
}

pines_redirect(pines_url('com_sales', 'cashcount/list'));

?>