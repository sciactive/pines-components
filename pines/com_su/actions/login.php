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

if ( empty($_REQUEST['username']) && empty($_REQUEST['pin']) ) {
	$pines->user_manager->print_login();
	return;
}
if ( gatekeeper() && $_REQUEST['username'] == $_SESSION['user']->username ) {
	pines_notice('Already logged in!');
	return;
}
if (!empty($_REQUEST['pin']) && $pines->config->com_su->allow_pins) {
	$users = $pines->user_manager->get_users();
	foreach ($users as $cur_user) {
		if (empty($cur_user->pin))
			continue;
		if ($_REQUEST['pin'] == $cur_user->pin) {
			$user = $cur_user;
			pines_log("Used PIN to access {$user->username}.", 'notice');
			break;
		}
	}
} else {
	$user = user::factory($_REQUEST['username']);
	if (!gatekeeper('com_su/nopassword') && !$user->check_password($_REQUEST['password']))
		unset($user);
}
if ( isset($user, $user->guid) ) {
	pines_log("Switching user from {$_SESSION['user']->username} to {$user->username}.", 'notice');
	if ($pines->user_manager->login($user)) {
		header('HTTP/1.1 303 See Other', true, 303);
		header('Location: '.pines_url());
	} else {
		pines_error('Could not switch users.');
		// Load the default component.
		action($pines->config->default_component, 'default');
	}
} else {
	pines_notice('Username and password not correct!');
	$pines->user_manager->print_login();
}

?>