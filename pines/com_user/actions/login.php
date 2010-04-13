<?php
/**
 * Log a user into the system.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( empty($_REQUEST['username']) ) {
	$pines->user_manager->print_login();
	return;
}
if ( gatekeeper() && $_REQUEST['username'] == $_SESSION['user']->username ) {
	pines_notice('Already logged in!');
	return;
}
$user = user::factory($_REQUEST['username']);
if ( isset($user->guid) && $user->check_password($_REQUEST['password']) ) {
	$pines->user_manager->login($user);
	if ( !empty($_REQUEST['url']) ) {
		header('Location: '.urldecode($_REQUEST['url']));
		exit;
	}
	// Load the user's default component.
	action($pines->config->default_component, 'default');
} else {
	pines_notice('Username and password not correct!');
	$pines->user_manager->print_login();
}

?>