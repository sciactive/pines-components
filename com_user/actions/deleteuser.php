<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/delete') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'manageusers', null, false));
	return;
}

if ( $config->user_manager->delete_user($_REQUEST['user_id']) )
	display_notice('User deleted successfully.');

$config->user_manager->list_users();
?>