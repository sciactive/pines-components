<?php
defined('D_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_user/new') || gatekeeper('com_user/manage') ) {
	$com_user_menu_id = $page->main_menu->add('User Manager');
	if ( gatekeeper('com_user/manage') )
		$page->main_menu->add('Users', $config->template->url('com_user', 'manageusers'), $com_user_menu_id);
		$page->main_menu->add('Groups', $config->template->url('com_user', 'managegroups'), $com_user_menu_id);
	if ( gatekeeper('com_user/new') )
		$page->main_menu->add('New User', $config->template->url('com_user', 'newuser'), $com_user_menu_id);
		$page->main_menu->add('New Group', $config->template->url('com_user', 'newgroup'), $com_user_menu_id);
}
if ( gatekeeper() ) $page->main_menu->add('Logout', $config->template->url('com_user', 'logout'));

?>