<?php
/**
 * Delete a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/deletegroup') )
	punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listgroups', null, false));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_group) {
	$cur_entity = group::factory((int) $cur_group);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_group;
}
if (empty($failed_deletes)) {
	display_notice('Selected group(s) deleted successfully.');
} else {
	display_error('Could not delete groups with given IDs: '.$failed_deletes);
}

$pines->user_manager->list_groups();
?>