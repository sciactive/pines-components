<?php
/**
 * Save changes to an issue type.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editissuetypes') )
	punt_user(null, pines_url('com_hrm', 'issue/list'));

if (isset($_REQUEST['id']) && (int) $_REQUEST['id'] != 0) {
	$issue_type = com_hrm_issue_type::factory((int) $_REQUEST['id']);
	if (!isset($issue_type->guid)) {
		pines_error('Requested issue type id is not accessible.');
		return;
	}
} else {
	$issue_type = com_hrm_issue_type::factory();
}
$issue_type->name = $_REQUEST['name'];
$issue_type->penalty = (float) $_REQUEST['penalty'];
$issue_type->description = $_REQUEST['description'];

if (empty($issue_type->name)) {
	pines_notice('Please provide a name for this issue type.');
	redirect(pines_url('com_hrm', 'issue/list'));
	return;
}
if (empty($issue_type->penalty)) {
	pines_notice('Please provide a penalty for this issue type.');
	redirect(pines_url('com_hrm', 'issue/list'));
	return;
}

if ($issue_type->save()) {
	pines_notice('Saved issue type ['.$issue_type->name.']');
} else {
	pines_error('Error saving issue type. Do you have permission?');
}

redirect(pines_url('com_hrm', 'issue/list'));

?>