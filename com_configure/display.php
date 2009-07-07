<?php
defined('D_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_configure/manage') || gatekeeper('com_configure/list') ) {
	$com_configure_menu_id = $page->main_menu->add('Configuration');
	if ( gatekeeper('com_configure/manage') )
		$page->main_menu->add('Manage', $config->template->url('com_configure', 'manage'), $com_configure_menu_id);
	if ( gatekeeper('com_configure/list') )
		$page->main_menu->add('List', $config->template->url('com_configure', 'list'), $com_configure_menu_id);
}

?>