<?php
/**
 * com_user's configuration defaults.
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
	array(
		'name' => 'create_admin',
		'cname' => 'Create Admin',
		'description' => 'Allow the creation of an admin user. When a user is created, if there are no other users in the system, he will be granted all abilities.',
		'value' => true,
	),
	array(
		'name' => 'allow_registration',
		'cname' => 'Allow User Registration',
		'description' => 'Allow users to register.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'one_step_registration',
		'cname' => 'One Step Registration',
		'description' => 'Allow users to register in one step.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'check_username',
		'cname' => 'Check Usernames',
		'description' => 'Notify immediately if a requested username is available. (This can technically be used to determine if a user exists on the system.)',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'user_fields',
		'cname' => 'User Account Fields',
		'description' => 'These will be the available fields for users. (Some fields, like username, can\'t be excluded.)',
		'value' => array('name', 'email', 'phone', 'fax', 'timezone', 'pin', 'address', 'additional_addresses', 'attributes'),
		'options' => array(
			'Name' => 'name',
			'Email' => 'email',
			'Phone Number' => 'phone',
			'Fax Number' => 'fax',
			'Timezone' => 'timezone',
			'PIN Code' => 'pin',
			'Address' => 'address',
			'Additional Addresses' => 'additional_addresses',
			'Attributes' => 'attributes',
		),
		'peruser' => true,
	),
	array(
		'name' => 'reg_fields',
		'cname' => 'Visible Registration Fields',
		'description' => 'These fields will be available for the user to fill in when they register.',
		'value' => array('name', 'email', 'phone', 'fax', 'timezone', 'address'),
		'options' => array(
			'Name' => 'name',
			'Email' => 'email',
			'Phone Number' => 'phone',
			'Fax Number' => 'fax',
			'Timezone' => 'timezone',
			'Address' => 'address',
		),
		'peruser' => true,
	),
	array(
		'name' => 'reg_message_welcome',
		'cname' => 'Registration Welcome Message',
		'description' => 'This message will be displayed to the user after they register.',
		'value' => 'You can begin using the system with the menu near the top of the page.',
		'peruser' => true,
	),
	array(
		'name' => 'confirm_email',
		'cname' => 'Confirm User Email Addresses',
		'description' => 'Confirm users\' email addresses upon registration/email change before allowing them to log in/change it.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'pw_recovery',
		'cname' => 'Allow Account Recovery',
		'description' => 'Allow users to recover their username and/or password through their registered email.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'pw_recovery_minutes',
		'cname' => 'Recovery Request Duration',
		'description' => 'How many minutes a recovery request is valid.',
		'value' => 240,
		'peruser' => true,
	),
	array(
		'name' => 'pw_empty',
		'cname' => 'Allow Empty Passwords',
		'description' => 'Allow users to have empty passwords.',
		'value' => false,
	),
	array(
		'name' => 'pw_method',
		'cname' => 'Password Storage Method',
		'description' => "Method used to store passwords. Salt is more secure if the database is compromised, but can't be used with SAWASC.\n\nPlain: store the password in plaintext.\nDigest: store the password's digest using a simple salt.\nSalt: store the password's digest using a complex, unique salt.",
		'value' => 'digest',
		'options' => array(
			'Plain' => 'plain',
			'Digest' => 'digest',
			'Salt' => 'salt'
		),
	),
	array(
		'name' => 'sawasc',
		'cname' => 'Enable SAWASC',
		'description' => 'SAWASC secures user authentication. If you do not host your site using SSL/TLS, you should enable this. However, it is not compatible with the "Salt" password storage method. See http://sawasc.sciactive.com/ for more information.',
		'value' => false,
	),
	array(
		'name' => 'sawasc_hash',
		'cname' => 'SAWASC Hash Function',
		'description' => 'Hash function to use during SAWASC authentication. If you don\'t know what this means, just leave it as the default.',
		'value' => 'whirlpool',
		'options' => array(
			'md5',
			'whirlpool',
		),
	),
	array(
		'name' => 'login_menu',
		'cname' => 'Show Login in Menu',
		'description' => 'Show a login button in the menu.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'login_menu_path',
		'cname' => 'Login Menu Path',
		'description' => 'The path of the login button in the menu.',
		'value' => 'main_menu/~login',
		'peruser' => true,
	),
	array(
		'name' => 'login_menu_text',
		'cname' => 'Login Menu Text',
		'description' => 'The text of the login button in the menu.',
		'value' => 'Log In',
		'peruser' => true,
	),
	array(
		'name' => 'referral_codes',
		'cname' => 'Enable Referral Codes',
		'description' => 'Enable users to enter referral codes.',
		'value' => false,
	),
	array(
		'name' => 'conditional_groups',
		'cname' => 'Conditional Groups',
		'description' => 'Allow groups to only provide abilities if conditions are met.',
		'value' => true,
	),
	array(
		'name' => 'highest_primary',
		'cname' => 'Highest Assignable Primary Group Parent',
		'description' => 'The GUID of the group above the highest groups allowed to be assigned as primary groups. Zero means all groups, and -1 means no groups.',
		'value' => 0,
		'peruser' => true,
	),
	array(
		'name' => 'highest_secondary',
		'cname' => 'Highest Assignable Secondary Group Parent',
		'description' => 'The GUID of the group above the highest groups allowed to be assigned as secondary groups. Zero means all groups, and -1 means no groups.',
		'value' => 0,
		'peruser' => true,
	),
	array(
		'name' => 'valid_chars',
		'cname' => 'Valid Characters',
		'description' => 'Only these characters can be used when creating usernames and groupnames.',
		'value' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-.',
	),
	array(
		'name' => 'valid_chars_notice',
		'cname' => 'Valid Characters Notice',
		'description' => 'When a user enters an invalid name, this message will be displayed.',
		'value' => 'Usernames and groupnames can only contain letters, numbers, underscore, dash, and period.',
		'peruser' => true,
	),
	array(
		'name' => 'valid_regex',
		'cname' => 'Valid Regex',
		'description' => 'Usernames and groupnames must match this regular expression. By default, this ensures that the name begins and ends with an alphanumeric. (To allow anything, use "/.*/")',
		'value' => '/^[a-zA-Z0-9].*[a-zA-Z0-9]$/',
	),
	array(
		'name' => 'valid_regex_notice',
		'cname' => 'Valid Regex Notice',
		'description' => 'When a user enters a name that doesn\'t match the regex, this message will be displayed.',
		'value' => 'Usernames and groupnames must begin and end with a letter or number.',
		'peruser' => true,
	),
	array (
		'name' => 'max_username_length',
		'cname' => 'Username Max Length',
		'description' => 'The maximum length for usernames. 0 for unlimited.',
		'value' => 0,
	),
	array (
		'name' => 'max_groupname_length',
		'cname' => 'Groupname Max Length',
		'description' => 'The maximum length for groupnames. 0 for unlimited.',
		'value' => 0,
	),
	array (
		'name' => 'min_pin_length',
		'cname' => 'User PIN Min Length',
		'description' => 'The minimum length for user PINs. 0 for no minimum.',
		'value' => 5,
	),
	/*
	array(
		'name' => 'resize_logos',
		'cname' => 'Resize Logos',
		'description' => 'Resize the group logos before saving them.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'logo_width',
		'cname' => 'Logo Width',
		'description' => 'If resizing logos, use this width.',
		'value' => 200,
		'peruser' => true,
	),
	array(
		'name' => 'logo_height',
		'cname' => 'Logo Height',
		'description' => 'If resizing logos, use this height.',
		'value' => 75,
		'peruser' => true,
	),
	*/
);

?>