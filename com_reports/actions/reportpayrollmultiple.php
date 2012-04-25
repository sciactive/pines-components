<?php
/**
 * Report multiple payrolls.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Kirk Johnson <kirk@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportpayroll') )
	punt_user(null, pines_url('com_reports', 'reportpayrollmultiple', $_REQUEST));

if (!empty($_REQUEST['start_date'])) {
	$start_date = $_REQUEST['start_date'];
	if (strpos($start_date, '-') === false)
		$start_date = format_date($start_date, 'date_sort');
	$start_date = strtotime($start_date.' 00:00:00');
} else {
	$start_date = strtotime('-1 week');
}
if (!empty($_REQUEST['end_date'])) {
	$end_date = $_REQUEST['end_date'];
	if (strpos($end_date, '-') === false)
		$end_date = format_date($end_date, 'date_sort');
	$end_date = strtotime($end_date.' 23:59:59') + 1;
} else {
	$end_date = strtotime('now');
}
if ($_REQUEST['all_time'] == 'true') {
	$start_date = null;
	$end_date = null;
}
if (!empty($_REQUEST['location']))
	$location = group::factory((int) $_REQUEST['location']);
$descendants = ($_REQUEST['descendants'] == 'true');

$ids = (array) explode(';', $_REQUEST['id']);
$hours_array = (array) explode(';', $_REQUEST['hours']);
$total_array = (array) explode(';', $_REQUEST['total']);
$payperhour_array = (array) explode(';', $_REQUEST['payperhour']);
$commission_array = (array) explode(';', $_REQUEST['commission']);
$salary_array = (array) explode(';', $_REQUEST['salary']);
$emp = com_hrm_employee::factory((int) $_REQUEST['id']);
$module = new module('com_reports', 'report_multiple_payroll', 'content');
$module->pages = array();
foreach ($ids as $key => $id) {
	$emp = com_hrm_employee::factory((int) $id);
	$hours = $hours_array[$key];
	$total = $total_array[$key];
	$payperhour = $payperhour_array[$key];
	$commission = $commission_array[$key];
	$commission = str_replace('$', '', $commission);
	$salary = $salary_array[$key];
	$cur_module = $pines->com_reports->report_payroll_individual($start_date, $end_date, $emp, $payperhour, $hours, $total, $salary, $commission);
	$cur_module->detach();
	$module->pages[] = $cur_module->render();
	unset($cur_module);
}

?>