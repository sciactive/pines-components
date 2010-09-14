<?php
/**
 * File an issue with an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/fileissue') )
	punt_user(null, pines_url('com_hrm', 'issue/file', array('items' => $_REQUEST['items'], 'issue_type' => $_REQUEST['issue_type'], 'date' => $_REQUEST['date'])));

$list = explode(',', $_REQUEST['items']);
$issue_type = com_hrm_issue_type::factory((int) $_REQUEST['issue_type']);
if (!isset($issue_type->guid)) {
	redirect(pines_url('com_hrm', 'employee/list'));
	return;
}

foreach ($list as $cur_item) {
    $cur_entity = com_hrm_employee::factory((int) $cur_item);
    if (isset($cur_entity->guid)) {
		$issue = com_hrm_issue::factory();
		$issue->employee = $cur_entity;
		$issue->date = strtotime($_REQUEST['date']);
		$issue->quantity = $_REQUEST['quantity'];
		$issue->issue_type = $issue_type;
		if (!empty($_REQUEST['comments']))
			$issue->comments[] = $_REQUEST['comments'];
		if ($issue->save()) {
			pines_log("Filed issue with employee: $cur_item", 'notice');
		} else {
			pines_log("GUID \"$cur_item\" could not be saved. Employee Issue could not be filed.", 'error');
			$failed_disposals .= (empty($failed_disposals) ? '' : ', ').$cur_item;
		}
    } else {
        pines_log("GUID \"$cur_item\" is not a valid employee. Employee Issue could not be filed.", 'error');
        $failed_disposals .= (empty($failed_disposals) ? '' : ', ').$cur_item;
    }
    unset($cur_entity);
}
if (empty($failed_disposals)) {
    pines_notice("Selected Employee Issue(s) successfully filed.");
} else {
    pines_error("Employee(s) with given ID(s) were not filed with issues : $failed_disposals");
}

redirect(pines_url('com_hrm', 'employee/list'));

?>