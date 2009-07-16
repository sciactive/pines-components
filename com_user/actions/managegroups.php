<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/manageg') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'managegroups', null, false));
	return;
}

$config->user_manager->list_groups();
?>