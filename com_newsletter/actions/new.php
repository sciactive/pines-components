<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/managemails') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_newsletter', 'list', null, false));
	return;
}

com_newsletter::edit_mail("New mail.", NULL, 'com_newsletter', 'edit');
?>
