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

if ( gatekeeper('com_user/new') || gatekeeper('com_user/manage') || gatekeeper('com_user/newg') || gatekeeper('com_user/manageg') ) {
	$com_user_menu_id = $page->main_menu->add('User Manager');
	if ( gatekeeper('com_user/manage') )
		$page->main_menu->add('Users', pines_url('com_user', 'manageusers'), $com_user_menu_id);
	if ( gatekeeper('com_user/new') )
		$page->main_menu->add('New User', pines_url('com_user', 'newuser'), $com_user_menu_id);
	if ( gatekeeper('com_user/manageg') )
		$page->main_menu->add('Groups', pines_url('com_user', 'managegroups'), $com_user_menu_id);
	if ( gatekeeper('com_user/newg') )
		$page->main_menu->add('New Group', pines_url('com_user', 'newgroup'), $com_user_menu_id);
}
if ( gatekeeper('com_user/self') ) $page->main_menu->add('My Account', pines_url('com_user', 'edituser', array('id' => $_SESSION['user_id'])));
if ( gatekeeper() ) $page->main_menu->add('Logout', pines_url('com_user', 'logout'));

?>