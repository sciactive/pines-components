<?php
/**
 * com_user's display control.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_user/newuser') || gatekeeper('com_user/listusers') || gatekeeper('com_user/newgroup') || gatekeeper('com_user/listgroups') ) {
	$com_user_menu_id = $config->page->main_menu->add('User Manager');
	if ( gatekeeper('com_user/listusers') )
		$config->page->main_menu->add('Users', pines_url('com_user', 'listusers'), $com_user_menu_id);
	if ( gatekeeper('com_user/newuser') )
		$config->page->main_menu->add('New User', pines_url('com_user', 'edituser'), $com_user_menu_id);
	if ( gatekeeper('com_user/listgroups') )
		$config->page->main_menu->add('Groups', pines_url('com_user', 'listgroups'), $com_user_menu_id);
	if ( gatekeeper('com_user/newgroup') )
		$config->page->main_menu->add('New Group', pines_url('com_user', 'editgroup'), $com_user_menu_id);
}
if ( gatekeeper('com_user/self') )
	$config->page->main_menu->add('My Account', pines_url('com_user', 'editself'));
if ( gatekeeper() )
	$config->page->main_menu->add('Logout', pines_url('com_user', 'logout'));

?>