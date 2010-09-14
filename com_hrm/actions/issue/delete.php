<?php
/**
 * Delete issue types.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/deleteissuetype') )
	punt_user(null, pines_url('com_hrm', 'issue/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_issue) {
	$cur_entity = com_hrm_issue_type::factory((int) $cur_issue);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_issue;
}
if (empty($failed_deletes)) {
	pines_notice('Selected issue(s) deleted successfully.');
} else {
	pines_error('Could not delete issue types with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_hrm', 'issue/list'));

?>