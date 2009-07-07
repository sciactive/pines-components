<?php
defined('D_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_newsletter/managemails') || gatekeeper('com_newsletter/managegroups') || gatekeeper('com_newsletter/send') ) {
	$com_newsletter_menu_id = $page->main_menu->add('Newsletter');
	if ( gatekeeper('com_newsletter/managemails') || gatekeeper('com_newsletter/send') ) {
		$page->main_menu->add('Mails Index', $config->template->url('com_newsletter', 'list'), $com_newsletter_menu_id);
		$page->main_menu->add('New Mail', $config->template->url('com_newsletter', 'new'), $com_newsletter_menu_id);
	}
}

?>