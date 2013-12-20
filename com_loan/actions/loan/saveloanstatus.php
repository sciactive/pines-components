<?php
/**
 * Save a status change on a loan.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
header('Content-Type: application/json');

if ( !gatekeeper('com_loan/changestatus') )
	$result = array('failed' => true);

// Check Permissions
if ($_REQUEST['status'] == 'active' && !gatekeeper('com_loan/activeloan') )
	$result = array('failed' => true);
if ($_REQUEST['status'] == 'paidoff' && !gatekeeper('com_loan/payoffloan') )
	$result = array('failed' => true);
if ($_REQUEST['status'] == 'writtenoff' && !gatekeeper('com_loan/writeoffloan') )
	$result = array('failed' => true);
if ($_REQUEST['status'] == 'cancelled' && !gatekeeper('com_loan/cancelloan') )
	$result = array('failed' => true);
if ($_REQUEST['status'] == 'sold' && !gatekeeper('com_loan/soldloan') )
	$result = array('failed' => true);

// Get Entity
if (!is_array($result) && isset($_REQUEST['id']) ) {
	$loan = com_loan_loan::factory((int) $_REQUEST['id']);
	if (!isset($loan->guid)) {
		$result = array('no_loan' => true);
	}
}
// Perform Action
if (!is_array($result)) {
	$loan->change_loan_tags($_REQUEST['status']);
}

// Save Results
if (!is_array($result) && $loan->save())
	$result = array('success' => true);
else
	$result = array('failed' => false);

$pines->page->override_doc(json_encode($result));

?>