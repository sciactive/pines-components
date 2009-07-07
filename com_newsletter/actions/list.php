<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/managemails') && !gatekeeper('com_newsletter/send') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_newsletter', 'list', null, false));
	return;
}

com_newsletter::list_mails('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '');
?>
