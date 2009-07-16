<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/deleteg') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'managegroups', null, false));
	return;
}

if ( $config->user_manager->delete_group($_REQUEST['group_id']) )
	display_notice('Group deleted successfully.');

$config->user_manager->list_groups();
?>