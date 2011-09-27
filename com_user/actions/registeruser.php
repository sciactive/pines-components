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
$user->grant('com_user/login');

$user->username = $_SESSION['com_user__tmpusername'];
$user->password($_SESSION['com_user__tmppassword']);
if ($pines->config->com_user->referral_codes)
	$user->referral_code = $_SESSION['com_user__tmpreferral_code'];
if (in_array('name', $pines->config->com_user->reg_fields)) {
	$user->name_first = $_REQUEST['name_first'];
	$user->name_middle = $_REQUEST['name_middle'];
	$user->name_last = $_REQUEST['name_last'];
	$user->name = $user->name_first.(!empty($user->name_middle) ? ' '.$user->name_middle : '').(!empty($user->name_last) ? ' '.$user->name_last : '');
}
if (in_array('email', $pines->config->com_user->reg_fields))
	$user->email = $_REQUEST['email'];
if (in_array('phone', $pines->config->com_user->reg_fields))
	$user->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
if (in_array('fax', $pines->config->com_user->reg_fields))
	$user->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
if (in_array('timezone', $pines->config->com_user->reg_fields))
	$user->timezone = $_REQUEST['timezone'];

// Location
if (in_array('address', $pines->config->com_user->reg_fields)) {
	$user->address_type = $_REQUEST['address_type'];
	$user->address_1 = $_REQUEST['address_1'];
	$user->address_2 = $_REQUEST['address_2'];
	$user->city = $_REQUEST['city'];
	$user->state = $_REQUEST['state'];
	$user->zip = $_REQUEST['zip'];
	$user->address_international = $_REQUEST['address_international'];
}

if ($pines->config->com_user->max_username_length > 0 && strlen($user->username) > $pines->config->com_user->max_username_length) {
	$user->register();
	pines_notice("Usernames must not exceed {$pines->config->com_user->max_username_length} characters.");
	return;
}
if (array_diff(str_split($user->username), str_split($pines->config->com_user->valid_chars))) {
	$user->register();
	pines_notice($pines->config->com_user->valid_chars_notice);
	return;
}
if (!preg_match($pines->config->com_user->valid_regex, $user->username)) {
	$user->register();
	pines_notice($pines->config->com_user->valid_regex_notice);
	return;
}
if (in_array('email', $pines->config->com_user->reg_fields)) {
	$test = $pines->entity_manager->get_entity(array('class' => user, 'skip_ac' => true), array('&', 'tag' => array('com_user', 'user'), 'strict' => array('email', $user->email)));
	if (isset($test) && !$user->is($test)) {
		$user->print_form();
		pines_notice('There is already a user with that email address. Please use a different email.');
		return;
	}
}
if (empty($user->password) && !$pines->config->com_user->pw_empty) {
	$user->register();
	pines_notice('Please specify a password.');
	return;
}

$user->group = $pines->entity_manager->get_entity(array('class' => group), array('&', 'tag' => array('com_user', 'group'), 'data' => array('default_primary', true)));
if (!isset($user->group->guid))
	unset($user->group);
$user->groups = (array) $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group'), 'data' => array('default_secondary', true)));

if ($pines->config->com_user->confirm_email) {
	// The user will be enabled after confirming their e-mail address.
	$user->disable();
	$user->secret = uniqid('', true);
} else {
	$user->enable();
}

// If create_admin is true and there are no other users, grant "system/all".
if ($pines->config->com_user->create_admin) {
	$other_users = $pines->entity_manager->get_entities(array('class' => user, 'limit' => 1), array('&', 'tag' => array('com_user', 'user')));
	// Make sure it's not just null, cause that means an error.
	if ($other_users === array()) {
		$user->grant('system/all');
		pines_notice("Welcome to {$pines->config->system_name}. Since this is the first user account, your account has been granted all abilities.");
	}
}

if ($user->save()) {
	pines_log('Registered user ['.$user->username.']');
	pines_session('write');
	unset($_SESSION['com_user__tmpusername']);
	unset($_SESSION['com_user__tmppassword']);
	unset($_SESSION['com_user__tmpreferral_code']);
	pines_session('close');
	if ($pines->config->com_user->confirm_email) {
		// Send the verification email.
		$link = '<a href="'.htmlspecialchars(pines_url('com_user', 'verifyuser', array('id' => $user->guid, 'type' => 'register', 'secret' => $user->secret, 'url' => $_REQUEST['url']), true)).'">'.htmlspecialchars(pines_url('com_user', 'verifyuser', array('id' => $user->guid, 'type' => 'register', 'secret' => $user->secret, 'url' => $_REQUEST['url']), true)).'</a>';
		$search = array(
			'{page_title}',
			'{site_name}',
			'{site_address}',
			'{link}',
			'{username}',
			'{name}',
			'{email}',
			'{phone}',
			'{fax}',
			'{timezone}',
			'{address}'
		);
		$replace = array(
			$pines->config->page_title,
			$pines->config->system_name,
			$pines->config->full_location,
			$link,
			htmlspecialchars($user->username),
			htmlspecialchars($user->name),
			htmlspecialchars($user->email),
			format_phone($user->phone),
			format_phone($user->fax),
			htmlspecialchars($user->timezone),
			htmlspecialchars($user->address_type == 'US' ? "{$user->address_1} {$user->address_2}\n{$user->city}, {$user->state} {$user->zip}" : $user->address_international)
		);
		$subject = str_replace($search, $replace, $pines->config->com_user->email_subject);
		$content = str_replace($search, $replace, $pines->config->com_user->email_content);
		$mail = com_mailer_mail::factory($pines->config->com_user->email_from_address, $user->email, $subject, $content);
		if ($mail->send()) {
			$note = new module('com_user', 'note_verify_email', 'content');
			$note->entity = $user;
		} else {
			pines_error('Couldn\'t send registration email.');
			return;
		}
	} else {
		$pines->user_manager->login($user);
		$note = new module('com_user', 'note_welcome', 'content');
		if ( !empty($_REQUEST['url']) ) {
			pines_redirect(urldecode($_REQUEST['url']));
			return;
		}
	}
} else {
	pines_error('Error registering user.');
}

?>