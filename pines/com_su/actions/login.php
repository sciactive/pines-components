<?php
/**
 * Log a user into the system.
 *
 * @package Pines
 * @subpackage com_su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( empty($_REQUEST['username']) ) {
$pines->user_manager->print_login();
	return;
}
if ( gatekeeper() && $_REQUEST['username'] == $_SESSION['user']->username ) {
	display_notice('Already logged in!');
	return;
}
$user = user::factory($_REQUEST['username']);
if ( isset($user->guid) && (gatekeeper('com_su/nopassword') || $user->check_password($_REQUEST['password'])) ) {
	$pines->user_manager->login($user);
	// Load the default component.
	action($pines->config->default_component, 'default');
} else {
	display_notice("Username and password not correct!");
	$pines->user_manager->print_login();
}

?>