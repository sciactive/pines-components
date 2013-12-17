<?php
/**
 * Save a collection code change on a loan.
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

if ( !gatekeeper('com_loan/changecollectioncode') )
	$result = array('failed' => true);

if ( isset($_REQUEST['id']) ) {
	$loan = com_loan_loan::factory((int) $_REQUEST['id']);
	if (!isset($loan->guid)) {
		$result = array('no_loan' => true);
	}
}

$loan->collection_code = $_REQUEST['code'];

if ($loan->save())
	$result = array('success' => true);
else
	$result = array('failed' => false);

$pines->page->override_doc(json_encode($result));

?>