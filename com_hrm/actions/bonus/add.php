<?php
/**
 * Give a bonus to an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editbonus') )
	punt_user(null, pines_url('com_hrm', 'bonus/add', array('employees' => $_REQUEST['employees'], 'issue_type' => $_REQUEST['issue_type'], 'date' => $_REQUEST['date'])));

$list = explode(',', $_REQUEST['employees']);
foreach ($list as $cur_employee) {
    $cur_entity = com_hrm_employee::factory((int) $cur_employee);
    if (isset($cur_entity->guid)) {
		$bonus = com_hrm_bonus::factory();
		$bonus->name = $_REQUEST['name'];
		$bonus->amount = (float) $_REQUEST['amount'];
		$bonus->date = strtotime($_REQUEST['date']);
		$bonus->employee = $cur_entity;
		$bonus->location = $cur_entity->group->guid ? $cur_entity->group : $_SESSION['user']->group;
		$bonus->comments = $_REQUEST['comments'];
		if ($bonus->save()) {
			pines_log("Granted bonus to employee: $cur_employee", 'notice');
		} else {
			pines_log("GUID \"$cur_item\" could not be saved. Employee bonus was not granted.", 'error');
			$failed_bonuses .= (empty($failed_bonuses) ? '' : ', ').$cur_employee;
		}
    } else {
        pines_log("GUID \"$cur_item\" is not a valid employee. Employee bonus was not granted.", 'error');
        $failed_bonuses .= (empty($failed_bonuses) ? '' : ', ').$cur_employee;
    }
    unset($cur_entity);
}
if (empty($failed_bonuses)) {
    pines_notice("Employee bonuses were successfully granted.");
} else {
    pines_error("Employees with given IDs were not granted bonuses : $failed_bonuses");
}

pines_redirect(pines_url('com_hrm', 'employee/list'));

?>