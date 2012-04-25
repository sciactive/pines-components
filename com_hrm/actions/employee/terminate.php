<?php
/**
 * Display a termination form.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/removeemployee') )
	punt_user(null, pines_url('com_hrm', 'employee/terminate', array('items' => $_REQUEST['items'], 'reason' => $_REQUEST['reason'], 'date' => $_REQUEST['date'])));

$list = explode(',', $_REQUEST['items']);
foreach ($list as $cur_item) {
    $cur_entity = user::factory((int) $cur_item);
    if (isset($cur_entity->guid)) {
		if ($_REQUEST['reason'] == 'rehired') {
			$action = 'Rehired';
			$cur_entity->enable();
			$cur_entity->hire_date = strtotime($_REQUEST['date']);
			$cur_entity->employment_history[] = array($cur_entity->hire_date, 'Rehired');
			unset($cur_entity->terminated);
			unset($cur_entity->terminated_date);
		} else {
			$action = 'Terminated';
			$cur_entity->disable();
			$cur_entity->terminated = $_REQUEST['reason'];
			$cur_entity->terminated_date = strtotime($_REQUEST['date']);
			$cur_entity->employment_history[] = array($cur_entity->terminated_date, 'Terminated - '.$cur_entity->terminated);
		}
		if ($cur_entity->save()) {
			pines_log("$action employee: $cur_item", 'notice');
		} else {
			pines_log("GUID \"$cur_item\" could not be saved. Employee not $action.", 'error');
			$failed_disposals .= (empty($failed_disposals) ? '' : ', ').$cur_item;
		}
    } else {
        pines_log("GUID \"$cur_item\" is not a valid employee. Employee not $action.", 'error');
        $failed_disposals .= (empty($failed_disposals) ? '' : ', ').$cur_item;
    }
    unset($cur_entity);
}
if (empty($failed_disposals)) {
    pines_notice("Selected employee(s) $action successfully.");
} else {
    pines_error("Employee(s) with given ID(s) were not $action : $failed_disposals");
}

pines_redirect(pines_url('com_hrm', 'employee/list', array('employed' => $_REQUEST['employed'])));

?>