<?php
/**
 * Delete a set of loans.
 *
 * @package Components
 * @subpackage loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_loan/deleteloan') )
	punt_user(null, pines_url('com_loan', 'loan/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_loan) {
	$cur_entity = com_loan_loan::factory((int) $cur_loan);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_loan;
}
if (empty($failed_deletes)) {
	pines_notice('Selected loan(s) deleted successfully.');
} else {
	pines_error('Could not delete loans with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_loan', 'loan/list'));

?>