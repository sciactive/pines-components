<?php
/**
 * Add an employee.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/addemployee') )
	punt_user(null, pines_url('com_hrm', 'employee/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_employee) {
	$cur_user = user::factory((int) $cur_employee);
	$cur_user->employee = true;
	$cur_user->hire_date = time();
	$cur_user->employment_history[] = array($cur_user->hire_date, 'Hired');

	if (!isset($cur_user->timeclock->guid)) {
		$cur_user->timeclock = com_hrm_timeclock::factory();
		$cur_user->timeclock->user = $cur_user;
		$cur_user->timeclock->group = $cur_user->group;
	}
	if ((array) $cur_user->employee_attributes !== $cur_user->employee_attributes)
		$cur_user->employee_attributes = array();
	if ((array) $cur_user->commissions !== $cur_user->commissions)
		$cur_user->commissions = array();
	if ( !isset($cur_user->guid) || !$cur_user->save() )
		$failed_adds .= (empty($failed_adds) ? '' : ', ').$cur_employee;
}
if (empty($failed_adds)) {
	pines_notice('Selected user(s) added successfully.');
} else {
	pines_error('Could not add users with given IDs: '.$failed_adds);
}

pines_redirect(pines_url('com_hrm', 'employee/list'));

?>