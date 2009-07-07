<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper() ) {
	$config->user_manager->punt_user("You are not logged in.", $config->template->url('com_about', null, null, false));
	return;
}
$com_about_mod1 = new module('content');
$com_about_mod1->title = "About ".$config->option_title." (Powered by ".$config->program_title.")";
$com_about_mod1->content($config->com_about->description."<br /><br />\n");
if ( $config->com_about->describe_self ) {
	$com_about_mod2 = new module('content');
	$com_about_mod2->title = "About ".$config->program_title;
	$com_about_mod2->content("<strong>Version ".$config->program_version."</strong><br /><br />\n");
	$com_about_mod2->content($config->program_title." is a <a href=\"http://sciactive.com/\">SciActive</a> project written by Hunter Perrin. It is a PHP application framework. Designed to be an extensible MVC based framework. The manager drops in components to add the functionality he desires. For example, if the manager wants to have a user management system, he simply drops in com_user. When com_user takes over user management for the system, it will prompt users to log in and only give them permissions they have been allowed.<br /><br />\n");
	$com_about_mod2->content("The admin can add functions using the premade components, or write his own components to provide additional functionality to the system. The system will have a dependency verifier, which will inform the admin if he is missing required components and where to get them. ".$config->program_title." was designed to allow maximum flexibility for the developer, while still providing a large enough base product to make development easy. The admin can choose whatever database environment he uses, even flat files, and thanks to the database abstraction layer, all the components will still work.\n");
}
?>