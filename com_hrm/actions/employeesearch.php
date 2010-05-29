<?php
/**
 * Search employees, returning JSON.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/listemployees') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'employeesearch', $_REQUEST));

$pines->page->override = true;

$query = strtolower($_REQUEST['q']);

if (empty($query)) {
	$employees = array();
} else {
	$employees = $pines->entity_manager->get_entities(array('class' => com_hrm_employee), array('&', 'tag' => array('com_hrm', 'employee')));
	if (!is_array($employees))
		$employees = array();
}

foreach ($employees as $key => &$cur_employee) {
	if (
		(strpos(strtolower($cur_employee->name_first), $query) !== false) ||
		(strpos(strtolower($cur_employee->name_last), $query) !== false) ||
		(strpos(strtolower($cur_employee->job_title), $query) !== false) ||
		(strpos(strtolower($cur_employee->email), $query) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_employee->phone_home, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_employee->phone_work, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_employee->phone_cell, preg_replace('/\D/', '', $query)) !== false)
		) {
		$json_struct = (object) array(
			'key' => $cur_employee->guid,
			'values' => array(
				$cur_employee->name_first,
				$cur_employee->name_last,
				$cur_employee->job_title,
				$cur_employee->email,
				$cur_employee->city,
				$cur_employee->state,
				$cur_employee->zip,
				format_phone($cur_employee->phone_cell)
			)
		);
		$cur_employee = $json_struct;
	} else {
		unset($employees[$key]);
	}
}

if (empty($employees))
	$employees = null;

$pines->page->override_doc(json_encode($employees));

?>