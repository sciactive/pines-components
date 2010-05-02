<?php
/**
 * com_hrm's information.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'HRM',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Human resource manager',
	'description' => 'Manage your employees. You can allow your HR manager to securely create employees with restricted priveleges. Includes a timeclock to track your employees\' working hours.',
	'abilities' => array(
		array('listusertemplates', 'List User Templates', 'User can see user templates.'),
		array('newusertemplate', 'Create User Templates', 'User can create new user templates.'),
		array('editusertemplate', 'Edit User Templates', 'User can edit current user templates.'),
		array('deleteusertemplate', 'Delete User Templates', 'User can delete current user templates.'),
		array('listemployees', 'List Employees', 'User can see employees.'),
		array('newemployee', 'Create Employees', 'User can create new employees.'),
		array('editemployee', 'Edit Employees', 'User can edit current employees.'),
		array('deleteemployee', 'Delete Employees', 'User can delete current employees.'),
		array('clock', 'Clock In/Out', 'User can use the employee timeclock to clock in. (If attached to employee.)'),
		array('viewownclock', 'View Own Timeclock', 'User can view their own timeclock.'),
		array('viewclock', 'View Timeclock', 'User can view the employee timeclock (including times).'),
		array('manageclock', 'Manage Timeclock', 'User can manage and edit the employee timeclock.'),
		array('requiressn', 'Require SSN', 'User must store Social Security Numbers for employees.'),
		array('showssn', 'Show SSN', 'User can see and edit Social Security Numbers.'),
		array('editcalendar', 'Edit Calendar', 'User can edit the group calendar.'),
		array('viewcalendar', 'View Calendar', 'User can view the group calendar.')
	),
);

?>