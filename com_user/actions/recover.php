<?php
/**
 * Print the account recovery form.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_user->pw_recovery)
	return 'error_404';

if (empty($_REQUEST['type'])) {
	$module = new module('com_user', 'recover', 'content');
	return;
}

switch ($_REQUEST['type']) {
	case 'password':
	default:
		$user = user::factory($_REQUEST['account']);
		break;
	case 'username':
		$user = $pines->entity_manager->get_entity(
				array('class' => user),
				array('&',
					'tag' => array('com_user', 'user', 'enabled'),
					'strict' => array('email', $_REQUEST['account'])
				)
			);
		break;
}

if (!isset($user) || !isset($user->guid) || !$user->has_tag('enabled') || !gatekeeper('com_user/login', $user)) {
	pines_error('Requested user id is not accessible.');
	$pines->user_manager->print_login();
	return;
}

// Create a unique secret.
$user->secret = uniqid('', true);
$user->secret_time = time();
if (!$user->save()) {
	pines_error('Couldn\'t save user secret.');
	return;
}

// Send the recovery email.
$link = '<a href="'.htmlspecialchars(pines_url('com_user', 'recoverpassword', array('id' => $user->guid, 'secret' => $user->secret), true)).'">'.htmlspecialchars(pines_url('com_user', 'recoverpassword', array('id' => $user->guid, 'secret' => $user->secret), true)).'</a>';
$search = array(
	'{page_title}',
	'{site_name}',
	'{site_address}',
	'{link}',
	'{minutes}',
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
	$pines->config->com_user->pw_recovery_minutes,
	htmlspecialchars($user->username),
	htmlspecialchars($user->name),
	htmlspecialchars($user->email),
	format_phone($user->phone),
	format_phone($user->fax),
	htmlspecialchars($user->timezone),
	htmlspecialchars($user->address_type == 'US' ? "{$user->address_1} {$user->address_2}\n{$user->city}, {$user->state} {$user->zip}" : $user->address_international)
);
$subject = str_replace($search, $replace, $pines->config->com_user->pw_recovery_email_subject);
$content = str_replace($search, $replace, $pines->config->com_user->pw_recovery_email_content);
$mail = com_mailer_mail::factory($pines->config->com_user->email_from_address, $user->email, $subject, $content);
if ($mail->send()) {
	pines_notice('We have sent an email to your registered email address. Please check your email to continue with account recovery.');
	redirect(pines_url());
	return;
} else {
	pines_error('Couldn\'t send recovery email.');
	return;
}

?>