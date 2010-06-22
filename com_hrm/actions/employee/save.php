<?php
/**
 * Save changes to an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editemployee') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'employee/list'));
$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
if (!isset($employee->guid)) {
	pines_error('Requested employee id is not accessible.');
	return;
}

// General
if ($pines->config->com_hrm->ssn_field)
	$employee->ssn = preg_replace('/\D/', '', $_REQUEST['ssn']);
$employee->job_title = $_REQUEST['job_title'];
$employee->description = $_REQUEST['description'];
$employee->color = $_REQUEST['color'];
$employee->workday_length = $_REQUEST['workday_length'] != '' ?  (int) $_REQUEST['workday_length'] : null;

// Attributes
$employee->employee_attributes = (array) json_decode($_REQUEST['attributes']);
foreach ($employee->employee_attributes as &$cur_attribute) {
	$array = array(
		'name' => $cur_attribute->values[0],
		'value' => $cur_attribute->values[1]
	);
	$cur_attribute = $array;
}
unset($cur_attribute);

if (gatekeeper('com_hrm/requiressn') && empty($employee->ssn)) {
	$employee->print_form();
	pines_notice('Please provide an SSN.');
	return;
}

if ($employee->save()) {
	pines_notice('Saved employee ['.$employee->name.']');
} else {
	pines_error('Error saving employee. Do you have permission?');
}

redirect(pines_url('com_hrm', 'employee/list'));

?>