<?php
/**
 * Give an adjustment of pay to an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Kirk Johnson <Kirk@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editadjustment') )
	punt_user(null, pines_url('com_hrm', 'ajustment/add', array('employees' => $_REQUEST['employees'], 'issue_type' => $_REQUEST['issue_type'], 'date' => $_REQUEST['date'])));

$list = explode(',', $_REQUEST['employees']);
foreach ($list as $cur_employee) {
    $cur_entity = com_hrm_employee::factory((int) $cur_employee);
    if (isset($cur_entity->guid)) {
		$adjustment = com_hrm_adjustment::factory();
		$adjustment->name = $_REQUEST['name'];
		$adjustment->amount = (float) $_REQUEST['amount'];
		$adjustment->date = strtotime($_REQUEST['date']);
		$adjustment->employee = $cur_entity;
		$adjustment->location = $cur_entity->group->guid ? $cur_entity->group : $_SESSION['user']->group;
		$adjustment->comments = $_REQUEST['comments'];
		if ($adjustment->save()) {
			pines_log("Granted adjustment to employee: $cur_employee", 'notice');
		} else {
			pines_log("GUID \"$cur_item\" could not be saved. Employee adjustment was not granted.", 'error');
			$failed_adjustment .= (empty($failed_adjustment) ? '' : ', ').$cur_employee;
		}
    } else {
        pines_log("GUID \"$cur_item\" is not a valid employee. Employee adjustment was not granted.", 'error');
        $failed_adjustment .= (empty($failed_adjustment) ? '' : ', ').$cur_employee;
    }
    unset($cur_entity);
}
if (empty($failed_adjustment)) {
    pines_notice("Employee adjustments were successfully granted.");
} else {
    pines_error("Employees with given IDs were not granted adjustments : $failed_adjustment");
}

pines_redirect(pines_url('com_hrm', 'employee/list'));

?>