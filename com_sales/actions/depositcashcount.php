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

// Default to the current cash count that is still open for this location.
if (!isset($_REQUEST['id'])) {
	$existing_counts = $pines->entity_manager->get_entities(array('ref' => array('group' => $_SESSION['user']->group), 'tags' => array('com_sales', 'cashcount'), 'class' => com_sales_cashcount));
	foreach ($existing_counts as $cur_count) {
		if (!in_array($cur_count->status, array('closed', 'flagged')))
			$cashcount = $cur_count;
	}
} else {
	$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
}

if (!isset($cashcount->guid)) {
	pines_error('Requested cash count id is not accessible.');
	$pines->com_sales->list_cashcounts();
	return;
}
if ( isset($cashcount->group->guid) && !$cashcount->group->is($_SESSION['user']->group) ) {
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