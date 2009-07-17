<?php
/**
 * com_configure's display control.
 *
 * @package XROOM
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('X_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_configure/manage') || gatekeeper('com_configure/list') ) {
	$com_configure_menu_id = $page->main_menu->add('Configuration');
	if ( gatekeeper('com_configure/manage') )
		$page->main_menu->add('Manage', $config->template->url('com_configure', 'manage'), $com_configure_menu_id);
	if ( gatekeeper('com_configure/list') )
		$page->main_menu->add('List', $config->template->url('com_configure', 'list'), $com_configure_menu_id);
}

?>