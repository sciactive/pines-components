<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/manage') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_configure', 'manage', null, false));
	return;
}

display_notice("Not implemented yet.");
//TODO: finish this configurator manager code
?>
