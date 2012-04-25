<?php
/**
 * Processes a cancelled loan.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_loan/cancelloan') )
		punt_user(null, pines_url('com_loan', 'loan/list'));
}

$loan = com_loan_loan::factory((int) $_REQUEST['id']);

// Check if entity exists.
if (!isset($loan->guid)) {
	pines_notice('The given ID could not be found.');
	pines_redirect(pines_url('com_loan', 'loan/list'));
	return;
}

$loan->status = "cancelled";

$loan->get_payments_array();
$loan->save();
pines_notice('Loan status changed to Cancelled on Loan ID '.$loan->id.'.');
pines_redirect(pines_url('com_loan', 'loan/list'));

?>