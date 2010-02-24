<?php
/**
 * com_hrm's common file.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->ability_manager->add('com_hrm', 'listusertemplates', 'List User Templates', 'User can see user templates.');
$pines->ability_manager->add('com_hrm', 'newusertemplate', 'Create User Templates', 'User can create new user templates.');
$pines->ability_manager->add('com_hrm', 'editusertemplate', 'Edit User Templates', 'User can edit current user templates.');
$pines->ability_manager->add('com_hrm', 'deleteusertemplate', 'Delete User Templates', 'User can delete current user templates.');
$pines->ability_manager->add('com_hrm', 'listemployees', 'List Employees', 'User can see employees.');
$pines->ability_manager->add('com_hrm', 'newemployee', 'Create Employees', 'User can create new employees.');
$pines->ability_manager->add('com_hrm', 'editemployee', 'Edit Employees', 'User can edit current employees.');
$pines->ability_manager->add('com_hrm', 'deleteemployee', 'Delete Employees', 'User can delete current employees.');
$pines->ability_manager->add('com_hrm', 'clock', 'Clock In/Out', 'User can use the employee timeclock to clock in. (If attached to employee.)');
$pines->ability_manager->add('com_hrm', 'viewownclock', 'View Own Timeclock', 'User can view their own timeclock.');
$pines->ability_manager->add('com_hrm', 'viewclock', 'View Timeclock', 'User can view the employee timeclock (including times).');
$pines->ability_manager->add('com_hrm', 'manageclock', 'Manage Timeclock', 'User can manage and edit the employee timeclock.');
$pines->ability_manager->add('com_hrm', 'requiressn', 'Require SSN', 'User must store Social Security Numbers for employees.');
$pines->ability_manager->add('com_hrm', 'showssn', 'Show SSN', 'User can see and edit Social Security Numbers.');
$pines->ability_manager->add('com_hrm', 'editcalendar', 'Edit Calendar', 'User can edit the group calendar.');
$pines->ability_manager->add('com_hrm', 'viewcalendar', 'View Calendar', 'User can view the group calendar.');

?>