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

if ( !gatekeeper('com_user/deleteg') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_user', 'managegroups', null, false));
	return;
}

$list = explode(',', $_REQUEST['group_id']);
foreach ($list as $cur_group) {
    if ( !$config->user_manager->delete_group($cur_group) )
        $failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_group;
}
if (empty($failed_deletes)) {
    display_notice('Selected group(s) deleted successfully.');
} else {
    display_error('Could not delete groups with given IDs: '.$failed_deletes);
}

$config->user_manager->list_groups();
?>