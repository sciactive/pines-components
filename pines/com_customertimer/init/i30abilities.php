<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->ability_manager->add('com_customertimer', 'listfloors', 'List Floors', 'User can see floors.');
$pines->ability_manager->add('com_customertimer', 'newfloor', 'Create Floors', 'User can create new floors.');
$pines->ability_manager->add('com_customertimer', 'editfloor', 'Edit Floors', 'User can edit current floors.');
$pines->ability_manager->add('com_customertimer', 'timefloor', 'Time Floors', 'User can access floor timers.');
$pines->ability_manager->add('com_customertimer', 'deletefloor', 'Delete Floors', 'User can delete current floors.');
$pines->ability_manager->add('com_customertimer', 'login', 'Login Users', 'User can log a customer in to the time tracker.');
$pines->ability_manager->add('com_customertimer', 'loginpwless', 'Bypass Passwords', 'User can log a customer in without providing its password.');
$pines->ability_manager->add('com_customertimer', 'logout', 'Logout Users', 'User can log a customer out of the time tracker.');

// Old abilities.
$pines->ability_manager->add('com_customertimer', 'viewstatus', 'View Customer Status', 'User can view the timer status of logged in customers.');
$pines->ability_manager->add('com_customertimer', 'notifystatus', 'Notify Customer Status', 'User is notified when customers are running out of points.');
$pines->ability_manager->add('com_customertimer', 'ignorestatus', 'Ignore Customer Status', 'Customer status is not monitored. Use this to cancel the above ability. Prevents unnecessary server load.');

?>