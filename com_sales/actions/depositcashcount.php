<?php
/**
 * Provide a form to deposit skims from a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/depositcashcount') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'depositcashcount', array('id' => $_REQUEST['id'])));

$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!isset($cashcount->guid)) {
	pines_error('Requested cash count id is not accessible.');
	$pines->com_sales->list_cashcounts();
	return;
}
if ( isset($cashcount->group->guid) && $cashcount->group->guid != $_SESSION['user']->group->guid ) {
	pines_notice('This cash count belongs to a different location.');
	$pines->com_sales->list_cashcounts();
	return;
}
if (!$cashcount->final) {
	pines_notice('This cash count has not been committed.');
	$pines->com_sales->list_cashcounts();
	return;
}
if ($cashcount->status == 'closed' || $cashcount->status == 'flagged') {
	pines_notice('This cash count has already been closed out.');
	$pines->com_sales->list_cashcounts();
	return;
}
$deposit = com_sales_cashcount_deposit::factory();
$deposit->cashcount = $cashcount;
$deposit->print_form();

?>