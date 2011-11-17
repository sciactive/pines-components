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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'HRM',
	'author' => 'SciActive',
	'version' => '1.0.1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Human resource manager',
	'description' => 'Manage your employees. Includes a timeclock to track your employees\' working hours.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'user_manager&entity_manager&editor',
		'component' => 'com_jquery&com_datetimepicker&com_pgrid&com_pnotify&com_pform&com_user'
	),
	'recommend' => array(
		'component' => 'com_modules&com_calendar'
	),
	'abilities' => array(
		array('listemployees', 'List Employees', 'User can see employees.'),
		array('addemployee', 'Add Employees', 'User can add new employees.'),
		array('editemployee', 'Edit Employees', 'User can edit current employees.'),
		array('showssn', 'Show SSN', 'User can see and edit Social Security Numbers.'),
		array('removeemployee', 'Remove Employees', 'User can remove current employees.'),
		array('listapplications', 'List Applications', 'User can see employment applications.'),
		array('editapplication', 'Edit Applications', 'User can edit employment applications.'),
		array('listadjustments', 'List Adjustments', 'User can see employee adjustments.'),
		array('editadjustment', 'Edit Adjustments', 'User can edit employee adjustments.'),
		array('deleteadjustment', 'Delete Adjustments', 'User can delete employee adjustments.'),
		array('listbonuses', 'List Bonuses', 'User can see employee bonuses.'),
		array('editbonus', 'Edit Bonuses', 'User can edit employee bonuses.'),
		array('deletebonus', 'Delete Bonuses', 'User can delete employee bonuses.'),
		array('listissuetypes', 'List Issue Types', 'User can see employee issue types.'),
		array('editissuetypes', 'Edit Issue Types', 'User can edit employee issue types.'),
		array('deleteissuetype', 'Delete Issue Types', 'User can delete employee issue types.'),
		array('fileissue', 'File Issues', 'User can file employee issues.'),
		array('resolveissue', 'Resolve Issues', 'User can resolve employee issues.'),
		array('viewownhistory', 'View Own History', 'User can view their own history.'),
		array('clock', 'Clock In/Out', 'User can use the employee timeclock to clock in. (If they are an employee.)'),
		array('viewownclock', 'View Own Timeclock', 'User can view their own timeclock.'),
		array('viewclock', 'View Timeclock', 'User can view the employee timeclock (including times).'),
		array('manageclock', 'Manage Timeclock', 'User can manage and edit the employee timeclock.'),
		array('managerto', 'Review Time Off', 'User can review and approve requests for time off.')
	),
);

?>