<?php
/**
 * Save changes to a new user registration.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_user->allow_registration)
	punt_user('User registration not allowed.');

if (empty($_SESSION['com_user__tmpusername']) || empty($_SESSION['com_user__tmpusername'])) {
	pines_notice('Username and password could not be recalled.');
	return;
}

$user = user::factory();

$user->username = $_SESSION['com_user__tmpusername'];
$user->password($_SESSION['com_user__tmppassword']);
$user->name = $_REQUEST['name'];
$user->email = $_REQUEST['email'];
$user->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
$user->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
$user->timezone = $_REQUEST['timezone'];

// Location
$user->address_type = $_REQUEST['address_type'];
$user->address_1 = $_REQUEST['address_1'];
$user->address_2 = $_REQUEST['address_2'];
$user->city = $_REQUEST['city'];
$user->state = $_REQUEST['state'];
$user->zip = $_REQUEST['zip'];
$user->address_international = $_REQUEST['address_international'];

if ($pines->com_user->max_username_length > 0 && strlen($user->username) > $pines->com_user->max_username_length) {
	$user->register();
	pines_notice("Usernames must not exceed {$pines->com_user->max_username_length} characters.");
	return;
}
if (empty($user->password) && !$pines->config->com_user->empty_pw) {
	$user->register();
	pines_notice('Please specify a password.');
	return;
}

if ($pines->config->com_user->confirm_email) {
	// The user will be enabled after confirming their e-mail address.
	$user->enabled = false;
	$user->secret = uniqid('', true);
	// Send the verification e-mail.
	$content = 'Thank you for registering.
				Please click the link below to activate your account.
				<a href="'.pines_url('com_user', 'verifyuser', array('id' => $user->guid, 'secret' => $user->secret)).'"></a>';
	$mail = com_mailer_mail::factory($pines->config->com_user->email_from_address, $user->email, 'Please verify your email address', $content);
	$mail->send();
} else {
	$user->enabled = true;
}

if ($user->save()) {
	pines_notice('Registered user ['.$user->username.']');
	pines_log('Registered user ['.$user->username.']');
	unset($_SESSION['com_user__tmpusername']);
	unset($_SESSION['com_user__tmppassword']);
} else {
	pines_error('Error registering user. Do you have permission?');
}

$user->registered();

?>