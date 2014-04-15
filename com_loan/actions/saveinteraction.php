<?php
/**
 * The action save a customer interaction on loans.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/newinteraction') )
		punt_user(null, pines_url('com_loan', 'loan/list'));

$pines->page->override = true;
header('Content-Type: application/json');

$loan_ids = $_REQUEST['loan_ids'];
$employee = $_REQUEST['employee'];
$status = $_REQUEST['status'];
$type = $_REQUEST['type'];
$comments = $_REQUEST['comments'];

if (empty($comments))
	$success = false;
elseif (!empty($loan_ids)) {
	if (count($loan_ids) > 1) {
		foreach ($loan_ids as $loan_id) {
			$add_interaction = $pines->com_loan->add_interaction($loan_id, $employee, $type, $status, $comments);
			if ($add_interaction)
				$success = true;
			else
				$success = false;
		}
	} else {
		$loan_id = $loan_ids; // To avoid confusion..
		$add_interaction = $pines->com_loan->add_interaction($loan_id, $employee, $type, $status, $comments);
		if ($add_interaction)
			$success = true;
		else
			$success = false;
	}
} else {
	$success = false;
}

if ($success)
	$pines->page->override_doc('true');
else
	$pines->page->override_doc('false');
return;
?>