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
		'name' => 'com_reports',
		'cname' => 'Reports Integration',
		'description' => 'Integrate with com_reports.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'com_calendar',
		'cname' => 'Calendar Integration',
		'description' => 'Integrate with com_calendar.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'com_sales',
		'cname' => 'POS Integration',
		'description' => 'Integrate with com_sales.',
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
		'name' => 'ssn_field_require',
		'cname' => 'Require SSN Field',
		'description' => 'Require Pines to store a Social Security Number for employees.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'employee_departments',
		'cname' => 'Department Names & Colors',
		'description' => 'These groups will show up in the calendar with their associated colors.',
		'value' => array('District:cornflowerblue', 'Managers:gainsboro', 'Sales Reps:gold', 'IT:blueviolet', 'Sales Support:olive'),
		'peruser' => true,
	),
	array(
		'name' => 'timeclock_verify_pin',
		'cname' => 'Verify PIN for Clocking In/Out',
		'description' => 'Verify the user\'s PIN when they clock in or out.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'pay_start',
		'cname' => 'Payroll Start',
		'description' => 'This is first payday logged in the system, and each payday will be based upon this date and the period.',
		'value' => '5/6/2011',
		'peruser' => true,
	),
	array(
		'name' => 'pay_period',
		'cname' => 'Payroll Period',
		'description' => 'This is how often payday occurs at the company.',
		'value' => '604800',
		'options' => array(
			'1 Week' => '604800',
			'2 Weeks' => '1209600',
			'1 Month' => '2419200',
		)
	),
	array(
		'name' => 'termination_reasons',
		'cname' => 'Termination Reasons',
		'description' => 'Uses this format: reason_name:Reason Description.',
		'value' => array(
			'attitude:Poor Attitude',
			'layoff:Laid Off',
			'misconduct:Misconduct',
			'performace:Poor Performance',
			'tardiness:Tardiness',
			'theft:Theft/Stealing',
			'subordinance:Subordinance',
			'quit:Quit',
			'other:Other',
		),
		'peruser' => true,
	),
);

?>