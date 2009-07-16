<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/newg') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'managegroups', null, false));
	return;
}

$config->user_manager->print_group_form('Editing New Group', 'com_user', 'savegroup');
?>