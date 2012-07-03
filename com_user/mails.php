<?php
/**
 * com_user's mails.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'verify_email' => array(
		'cname' => 'Verify Email',
		'description' => 'This email is sent to a new user to let them verify their address.',
		'view' => 'mails/verify_email',
		'has_recipient' => true,
		'unsubscribe' => false,
		'macros' => array(
			'verify_link' => 'The URL to verify the email address, to be used in a link.',
			'to_phone' => 'The recipient\'s phone number.',
			'to_fax' => 'The recipient\'s fax number.',
			'to_timezone' => 'The recipient\'s timezone.',
			'to_address' => 'The recipient\'s address.',
		),
	),
	'verify_email_change' => array(
		'cname' => 'Verify Email Change',
		'description' => 'This email is sent to a user\'s new email when they change their email to let them verify their new address.',
		'view' => 'mails/verify_email_change',
		'has_recipient' => true,
		'unsubscribe' => false,
		'macros' => array(
			'verify_link' => 'The URL to verify the email address, to be used in a link.',
			'old_email' => 'The old email address.',
			'new_email' => 'The new email address.',
			'to_phone' => 'The recipient\'s phone number.',
			'to_fax' => 'The recipient\'s fax number.',
			'to_timezone' => 'The recipient\'s timezone.',
			'to_address' => 'The recipient\'s address.',
		),
	),
	'cancel_email_change' => array(
		'cname' => 'Cancel Email Change',
		'description' => 'This email is sent to a user\'s old email when they change their email to let them cancel their change.',
		'view' => 'mails/cancel_email_change',
		'has_recipient' => true,
		'unsubscribe' => false,
		'macros' => array(
			'cancel_link' => 'The URL to cancel the change, to be used in a link.',
			'old_email' => 'The old email address.',
			'new_email' => 'The new email address.',
			'to_phone' => 'The recipient\'s phone number.',
			'to_fax' => 'The recipient\'s fax number.',
			'to_timezone' => 'The recipient\'s timezone.',
			'to_address' => 'The recipient\'s address.',
		),
	),
	'recover_account' => array(
		'cname' => 'Recover Account',
		'description' => 'This email is sent when a user can\'t access their account so they can recover their username and/or password.',
		'view' => 'mails/recover_account',
		'has_recipient' => true,
		'unsubscribe' => false,
		'macros' => array(
			'recover_link' => 'The URL to change their password, to be used in a link.',
			'minutes' => 'How many minutes a recovery request is valid.',
			'to_phone' => 'The recipient\'s phone number.',
			'to_fax' => 'The recipient\'s fax number.',
			'to_timezone' => 'The recipient\'s timezone.',
			'to_address' => 'The recipient\'s address.',
		),
	),
);

?>