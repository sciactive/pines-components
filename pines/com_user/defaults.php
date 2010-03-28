<?php
/**
 * com_user's configuration defaults.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'empty_pw',
		'cname' => 'Empty Passwords',
		'description' => 'Allow users to have empty passwords.',
		'value' => false,
	),
	array(
		'name' => 'create_admin',
		'cname' => 'Create Admin',
		'description' => 'Allow the creation of an admin user.',
		'value' => true,
	),
	array(
		'name' => 'create_admin_secret',
		'cname' => 'Create Admin Secret',
		'description' => 'The secret necessary to create an admin user.',
		'value' => '874jdiv8',
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
	array(
		'name' => 'resize_logos',
		'cname' => 'Resize Logos',
		'description' => 'Resize the group logos before saving them.',
		'value' => false,
	),
	array(
		'name' => 'logo_width',
		'cname' => 'Logo Width',
		'description' => 'If resizing logos, use this width.',
		'value' => 200,
	),
	array(
		'name' => 'logo_height',
		'cname' => 'Logo Height',
		'description' => 'If resizing logos, use this height.',
		'value' => 75,
	),
);

?>