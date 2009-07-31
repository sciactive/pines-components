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
$config->ability_manager->add('com_user', 'default_component', 'Change Default Component', 'User can change default component.');
$config->ability_manager->add('com_user', 'new', 'Create Users', 'Let user create new users.');
$config->ability_manager->add('com_user', 'manage', 'Manage Users', 'Let user see and manage other users. Required to access the below abilities.');
$config->ability_manager->add('com_user', 'edit', 'Edit Users', 'Let user edit other users\' details.');
$config->ability_manager->add('com_user', 'delete', 'Delete Users', 'Let user delete other users.');
$config->ability_manager->add('com_user', 'assigng', 'Assign Groups', 'Let user assign users to groups, possibly granting them more abilities.');
$config->ability_manager->add('com_user', 'newg', 'Create Groups', 'Let user create new groups.');
$config->ability_manager->add('com_user', 'manageg', 'Manage Groups', 'Let user see and manage groups. Required to access the below abilities.');
$config->ability_manager->add('com_user', 'editg', 'Edit Groups', 'Let user edit groups\' details.');
$config->ability_manager->add('com_user', 'deleteg', 'Delete Groups', 'Let user delete groups.');
$config->ability_manager->add('com_user', 'abilities', 'Manage Abilities', 'Let user manage other users\' and his own abilities.');

if ( isset($_SESSION['user_id']) ) {
    $config->user_manager->fill_session();
}

?>