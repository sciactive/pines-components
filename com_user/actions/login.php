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

if ( $_REQUEST['login_register'] == 'register' ) {
	$test = user::factory($_REQUEST['username']);
	if (isset($test->guid)) {
		pines_notice('There is already a user with that username. Please choose a different username.');
		$pines->user_manager->print_login('content');
		return;
	}
	$user = user::factory();
	$_SESSION['com_user__tmpusername'] = $_REQUEST['username'];
	$_SESSION['com_user__tmppassword'] = $_REQUEST['password'];
	$user->register();
	return;
}

if ( gatekeeper() && $_REQUEST['username'] == $_SESSION['user']->username ) {
	pines_notice('Already logged in!');
	redirect(pines_url());
	return;
}
$user = user::factory($_REQUEST['username']);
if ( isset($user->guid) && $user->check_password($_REQUEST['password']) ) {
	$pines->user_manager->login($user);
	if ( !empty($_REQUEST['url']) ) {
		redirect(urldecode($_REQUEST['url']));
		exit;
	}
	// Load the default component.
	redirect(pines_url());
} else {
	pines_notice('Incorrect username/password!');
	$pines->user_manager->print_login();
}

?>