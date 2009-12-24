<?php
/**
 * com_customer_timer's common file.
 *
 * @package Pines
 * @subpackage com_customer_timer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->ability_manager->add('com_customer_timer', 'viewstatus', 'View Customer Status', 'User can view the timer status of logged in customers.');
$config->ability_manager->add('com_customer_timer', 'login', 'Login Users', 'User can log a customer in to the time tracker.');
$config->ability_manager->add('com_customer_timer', 'forcelogout', 'Force Logout', 'User can log a customer out of the time tracker.');

?>