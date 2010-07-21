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

if ( $pines->config->com_user->allow_registration && $_REQUEST['login_register'] == 'register' ) {
	if (empty($_REQUEST['username'])) {
		pines_notice('Username is a required field.');
		$pines->user_manager->print_login();
		return;
	}
	if (empty($_REQUEST['password']) && !$pines->config->com_user->empty_pw) {
		pines_notice('Password is a required field.');
		$pines->user_manager->print_login();
		return;
	}
	$test = user::factory($_REQUEST['username']);
	if (isset($test->guid)) {
		pines_notice('The username you requested is already taken. Please choose a different username.');
		$pines->user_manager->print_login();
		return;
	}
	$user = user::factory();
	$_SESSION['com_user__tmpusername'] = $_REQUEST['username'];
	$_SESSION['com_user__tmppassword'] = $_REQUEST['password'];
	$user->print_register();
	return;
}

if ( gatekeeper() && $_REQUEST['username'] == $_SESSION['user']->username ) {
	pines_notice('You are already logged in.');
	redirect(pines_url());
	return;
}
$user = user::factory($_REQUEST['username']);
if ( isset($user->guid) && $user->check_password($_REQUEST['password']) && $pines->user_manager->login($user) ) {
	if ( !empty($_REQUEST['url']) ) {
		redirect(urldecode($_REQUEST['url']));
		return;
	}
	// Load the default component.
	redirect(pines_url());
} else {
	pines_notice('Incorrect username/password.');
	$pines->user_manager->print_login();
}

?>