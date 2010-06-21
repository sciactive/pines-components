<?php
/**
 * com_hrm's configuration defaults.
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
	array(
		'name' => 'global_user_templates',
		'cname' => 'Globalize User Templates',
		'description' => 'Ensure that every user can access all user templates by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'ssn_field',
		'cname' => 'SSN Field',
		'description' => 'Allow Pines to store a Social Security Number for employees.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'employee_departments',
		'cname' => 'Department Names & Colors',
		'description' => 'These groups will show up in the calendar with their associated colors.',
		'value' => 'Entire Staff:cornflowerblue, Sales:gold, IT:blueviolet, Support:olive',
		'peruser' => true,
	),
	array(
		'name' => 'workday_length',
		'cname' => 'Default Workday Length',
		'description' => 'The amount of work hours in a full workday.',
		'value' => 8,
		'peruser' => true,
	),
);

?>