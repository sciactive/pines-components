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
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'User Manager',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'services' => array('user_manager'),
	'short_description' => 'Entity based user manager',
	'description' => 'Manages system users, groups, and abilities. Uses an entity manager as a storage backend.',
	'abilities' => array(
		array('login', 'Login', 'User can login to the system.'),
		array('self', 'Change Info', 'User can change his own information.'),
		array('listusers', 'List Users', 'User can see users.'),
		array('newuser', 'Create Users', 'User can create new users.'),
		array('edituser', 'Edit Users', 'User can edit other users.'),
		array('deleteuser', 'Delete Users', 'User can delete other users.'),
		array('default_component', 'Change Default Component', 'User can change users\' default component.'),
		array('abilities', 'Manage Abilities', 'Let user manage other users\' and his own abilities.'),
		array('listgroups', 'List Groups', 'User can see groups.'),
		array('newgroup', 'Create Groups', 'User can create new groups.'),
		array('editgroup', 'Edit Groups', 'User can edit other groups.'),
		array('deletegroup', 'Delete Groups', 'User can delete other groups.'),
		array('assigngroup', 'Assign Groups', 'User can assign users to groups, possibly granting them more abilities.')
	),
);

?>