<?php
/**
 * com_user's common file.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->ability_manager->add('com_user', 'login', 'Login', 'User can login to the system.');
$config->ability_manager->add('com_user', 'self', 'Change Info', 'User can change his own information.');
$config->ability_manager->add('com_user', 'manageusers', 'Manage Users', 'User can see and manage other users.');
$config->ability_manager->add('com_user', 'newuser', 'Create Users', 'User can create new users.');
$config->ability_manager->add('com_user', 'edituser', 'Edit Users', 'User can edit other users.');
$config->ability_manager->add('com_user', 'deleteuser', 'Delete Users', 'User can delete other users.');
$config->ability_manager->add('com_user', 'default_component', 'Change Default Component', 'User can change users\' default component.');
$config->ability_manager->add('com_user', 'abilities', 'Manage Abilities', 'Let user manage other users\' and his own abilities.');
$config->ability_manager->add('com_user', 'managegroups', 'Manage Groups', 'User can see and manage other groups.');
$config->ability_manager->add('com_user', 'newgroup', 'Create Groups', 'User can create new groups.');
$config->ability_manager->add('com_user', 'editgroup', 'Edit Groups', 'User can edit other groups.');
$config->ability_manager->add('com_user', 'deletegroup', 'Delete Groups', 'User can delete other groups.');
$config->ability_manager->add('com_user', 'assigngroup', 'Assign Groups', 'User can assign users to groups, possibly granting them more abilities.');

if ( isset($_SESSION['user_id']) )
	$config->user_manager->fill_session();

?>