<?php
/**
 * Delete an adjustment of pay to an employee.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Kirk Johnson <Kirk@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/deleteadjustment') )
	punt_user(null, pines_url('com_hrm', 'issue/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_adjustment) {
	$cur_entity = com_hrm_adjustment::factory((int) $cur_adjustment);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_issue;
}
if (empty($failed_deletes)) {
	pines_notice('Selected adjustment deleted successfully.');
} else {
	pines_error('Could not delete adjustment with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_hrm', 'adjustment/list'));

?>