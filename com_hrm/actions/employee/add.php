<?php
/**
 * Add an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/addemployee') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'employee/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_employee) {
	$cur_user = user::factory((int) $cur_employee);
	$cur_user->employee = true;
	if ((array) $cur_user->timeclock !== $cur_user->timeclock)
		$cur_user->timeclock = array();
	if ((array) $cur_user->employee_attributes !== $cur_user->employee_attributes)
		$cur_user->employee_attributes = array();
	if ( !isset($cur_user->guid) || !$cur_user->save() )
		$failed_adds .= (empty($failed_adds) ? '' : ', ').$cur_employee;
}
if (empty($failed_adds)) {
	pines_notice('Selected user(s) added successfully.');
} else {
	pines_error('Could not add users with given IDs: '.$failed_adds);
}

redirect(pines_url('com_hrm', 'employee/list'));

?>