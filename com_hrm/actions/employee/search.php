<?php
/**
 * Search employees, returning JSON.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/listemployees') )
	punt_user(null, pines_url('com_hrm', 'employee/search', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$query = strtolower($_REQUEST['q']);

if (empty($query)) {
	$employees = array();
} else {
	$employees = (array) $pines->com_hrm->get_employees();
}

foreach ($employees as $key => &$cur_employee) {
	if (
		(strpos(strtolower($cur_employee->name), $query) !== false) ||
		(strpos(strtolower($cur_employee->job_title), $query) !== false) ||
		(strpos(strtolower($cur_employee->email), $query) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_employee->phone, preg_replace('/\D/', '', $query)) !== false)
		) {
		$json_struct = (object) array(
			'guid'			=> $cur_employee->guid,
			'username'		=> $cur_employee->username,
			'name'			=> $cur_employee->name,
			'title'			=> $cur_employee->job_title,
			'email'			=> $cur_employee->email,
			'city'			=> $cur_employee->city,
			'state'			=> $cur_employee->state,
			'zip'			=> $cur_employee->zip,
			'phone'			=> format_phone($cur_employee->phone),
			'location_name'	=> $cur_employee->group->name
		);
		$cur_employee = $json_struct;
	} else {
		unset($employees[$key]);
	}
}
unset($cur_employee);

// If employees were removed, the array needs to be re-indexed.
$employees = array_values($employees);

if (empty($employees))
	$employees = null;

$pines->page->override_doc(json_encode($employees));

?>