<?php
/**
 * Assign a countsheet to an employee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('com_sales/assigncountsheet') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'assigncountsheet', array('employee' => $_REQUEST['employee'])));

if (preg_match('/^\d+/', $_REQUEST['employee'])) {
	$employee = com_hrm_employee::factory(intval($_REQUEST['employee']));
	if (is_null($employee->guid))
		$employee = null;
}

if (!isset($employee) || isset($employee->user_account->guid)) {
	pines_error('Requested employee id is not accessible.');
	$pines->com_sales->list_countsheets();
	return;
}
$employee->user_account->com_sales_task_countsheet = true;
if ($employee->user_account->save()) {
	pines_notice('Countsheet Assigned to ['.$employee->name_first.' '.$employee->name_last.']');
} else {
	pines_error('Error saving employee account. Do you have permission?');
}
$pines->com_sales->list_countsheets();
?>