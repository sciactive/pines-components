<?php
/**
 * Save approval changes to a cash count.
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

if ( !gatekeeper('com_sales/approvecashcount') )
	punt_user(null, pines_url('com_sales', 'cashcount/approve'));

$cashcount = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!isset($cashcount->guid)) {
	pines_error('Requested cash count id is not accessible.');
	return;
}

$cashcount->status = $_REQUEST['status'];
$cashcount->review_comments = $_REQUEST['review_comments'];

if (!$cashcount->final && $cashcount->status == 'closed') {
	pines_notice('You cannot close a cash count until it has been committed.');
	$cashcount->print_review();
	return;
}

if ($cashcount->save()) {
	switch ($cashcount->status) {
		case 'closed':
			pines_notice('Closed Cash Count ['.$cashcount->guid.']');
			break;
		case 'flagged':
			pines_notice('Flagged Cash Count ['.$cashcount->guid.']');
			break;
		default:
			pines_notice('Saved Cash Count ['.$cashcount->guid.']');
			break;
	}
} else {
	pines_error('Error saving Cash Count. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'cashcount/list'));

?>