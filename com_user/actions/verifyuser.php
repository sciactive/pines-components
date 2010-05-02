<?php
/**
 * Verify a newly registered user's e-mail address.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$user = user::factory((int) $_REQUEST['id']);

if (isset($user->guid)) {
	pines_notice('The specified user id is not available.');
	$pines->com_user->print_login();
	return;
}

if ($_REQUEST['id'] != $user->secret) {
	pines_notice('The secret code given does not match this user.');
	$pines->com_user->print_login();
	return;
}
$user->enabled = true;

if ($user->save()) {
	pines_notice('Validated user ['.$user->username.']');
	pines_log('Validated user ['.$user->username.']');
} else {
	pines_error('Error registering user. Do you have permission?');
}

$pines->com_user->login($user);

?>