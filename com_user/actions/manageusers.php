<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/manage') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'manageusers', null, false));
	return;
}

$config->user_manager->list_users();
?>