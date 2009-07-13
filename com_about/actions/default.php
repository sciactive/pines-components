<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper() ) {
	$config->user_manager->punt_user("You are not logged in.", $config->template->url('com_about', null, null, false));
	return;
}
$com_about_mod1 = new module('com_about', 'about1', 'content');
$com_about_mod1->title = "About ".$config->option_title." (Powered by ".$config->program_title.")";
if ( $config->com_about->describe_self ) {
	$com_about_mod2 = new module('com_about', 'about2', 'content');
	$com_about_mod2->title = "About ".$config->program_title;
}
?>