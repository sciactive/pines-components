<?php
/**
 * com_calendar's information.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Calendar',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Company Calendar',
	'description' => 'The company calendar can be used to manage employees and customers.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'user_manager&entity_manager&editor',
		'component' => 'com_jquery&com_pnotify&com_jstree&com_ptags&com_hrm'
	),
	'abilities' => array(
		array('editcalendar', 'Edit Calendar', 'User can edit the group calendar.'),
		array('viewcalendar', 'View Calendar', 'User can view the group calendar.'),
		array('managecalendar', 'Manage Calendar', 'User can view private events and schedules for other employee.'),
		array('editappointments', 'Edit Appointments', 'User can edit the appointments.')
	),
);

?>