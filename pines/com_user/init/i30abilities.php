<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->ability_manager->add('com_user', 'login', 'Login', 'User can login to the system.');
$pines->ability_manager->add('com_user', 'self', 'Change Info', 'User can change his own information.');
$pines->ability_manager->add('com_user', 'listusers', 'List Users', 'User can see users.');
$pines->ability_manager->add('com_user', 'newuser', 'Create Users', 'User can create new users.');
$pines->ability_manager->add('com_user', 'edituser', 'Edit Users', 'User can edit other users.');
$pines->ability_manager->add('com_user', 'deleteuser', 'Delete Users', 'User can delete other users.');
$pines->ability_manager->add('com_user', 'default_component', 'Change Default Component', 'User can change users\' default component.');
$pines->ability_manager->add('com_user', 'abilities', 'Manage Abilities', 'Let user manage other users\' and his own abilities.');
$pines->ability_manager->add('com_user', 'listgroups', 'List Groups', 'User can see groups.');
$pines->ability_manager->add('com_user', 'newgroup', 'Create Groups', 'User can create new groups.');
$pines->ability_manager->add('com_user', 'editgroup', 'Edit Groups', 'User can edit other groups.');
$pines->ability_manager->add('com_user', 'deletegroup', 'Delete Groups', 'User can delete other groups.');
$pines->ability_manager->add('com_user', 'assigngroup', 'Assign Groups', 'User can assign users to groups, possibly granting them more abilities.');

?>