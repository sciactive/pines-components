<?php
/**
 * com_customertimer's information.
 *
 * @package Components
 * @subpackage customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Customer Timer',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Customer account timer',
	'description' => 'Allows the use of com_customer\'s membership and point tracking feature to run a service that requires customers to buy time, such as an internet cafe.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager&uploader',
		'component' => 'com_customer&com_jquery&com_bootstrap&com_pgrid&com_pnotify&com_pform',
		'package' => 'com_customertimer-data'
	),
	'abilities' => array(
		array('listfloors', 'List Floors', 'User can see floors.'),
		array('newfloor', 'Create Floors', 'User can create new floors.'),
		array('editfloor', 'Edit Floors', 'User can edit current floors.'),
		array('timefloor', 'Time Floors', 'User can access floor timers.'),
		array('deletefloor', 'Delete Floors', 'User can delete current floors.'),
		array('login', 'Login Users', 'User can log a customer in to the time tracker.'),
		array('loginpwless', 'Bypass Passwords', 'User can log a customer in without providing its password.'),
		array('logout', 'Logout Users', 'User can log a customer out of the time tracker.')
	),
);

?>