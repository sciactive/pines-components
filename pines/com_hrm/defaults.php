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
		'name' => 'global_employees',
		'cname' => 'Globalize Employees',
		'description' => 'Ensure that every user can access all employees by setting the "other" access control to read.',
		'value' => true,
	),
	array(
		'name' => 'global_user_templates',
		'cname' => 'Globalize User Templates',
		'description' => 'Ensure that every user can access all user templates by setting the "other" access control to read.',
		'value' => true,
	),
	array(
		'name' => 'ssn_field',
		'cname' => 'SSN Field',
		'description' => 'Allow Pines to store a Social Security Number for employees.',
		'value' => true,
	),
	array(
		'name' => 'allow_attach',
		'cname' => 'Allow User Attach',
		'description' => 'Allow users to be attached to employees.',
		'value' => true,
	),
	array(
		'name' => 'employee_departments',
		'cname' => 'Department Names & Colors',
		'description' => 'These groups will show up in the calendar with their associated colors.',
		'value' => 'Company Wide:cornflowerblue, Corporate:gold, IT:blueviolet, Sales:olive',
	),
);

?>