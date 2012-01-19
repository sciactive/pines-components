<?php
/**
 * com_user's information.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'User Manager',
	'author' => 'SciActive',
	'version' => '1.0.1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('user_manager'),
	'short_description' => 'Entity based user manager',
	'description' => 'Manages system users, groups, and abilities. Uses an entity manager as a storage backend.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager&uploader&icons',
		'component' => 'com_mailer&com_jquery&com_pgrid&com_pnotify&com_pform', //&com_jstree
		'package' => 'com_user-data'
	),
	'recommend' => array(
		'component' => 'com_modules'
	),
	'abilities' => array(
		array('login', 'Login', 'User can login to the system.'),
		array('self', 'Change Info', 'User can change his own information. Email address changes may be subject to confirmation.'),
		array('listusers', 'List Users', 'User can see users.'),
		array('newuser', 'Create Users', 'User can create new users.'),
		array('edituser', 'Edit Users', 'User can edit other users. Email address changes take place immediately.'),
		array('deleteuser', 'Delete Users', 'User can delete other users.'),
		array('enabling', 'Manage Enabling', 'User can enable and disable users and groups.'),
		array('usernames', 'Manage Usernames', 'User can change usernames and groupnames.'),
		array('abilities', 'Manage Abilities', 'Let user manage other users\' and his own abilities.'),
		array('assignpin', 'Assign PIN', 'User can assign PIN codes for users.'),
		array('conditions', 'Manage Conditions', 'Let user manage user and group conditions.'),
		array('listgroups', 'List Groups', 'User can see groups.'),
		array('newgroup', 'Create Groups', 'User can create new groups.'),
		array('editgroup', 'Edit Groups', 'User can edit other groups.'),
		array('deletegroup', 'Delete Groups', 'User can delete other groups.'),
		array('assigngroup', 'Assign Groups', 'User can assign users to groups, possibly granting them more abilities.'),
		array('defaultgroups', 'Change Default Groups', 'User can change which groups will be assigned to new users.')
	),
);

?>