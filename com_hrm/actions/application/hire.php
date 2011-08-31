<?php
/**
 * Hire an employee based on an application.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/addemployee') )
	punt_user(null, pines_url('com_hrm', 'application/list'));

$list = explode(',', $_REQUEST['items']);

foreach ($list as $cur_application_id) {
	$cur_application = com_hrm_application::factory((int) $cur_application_id);
	if ($cur_application->status == 'hired')
		continue;
	if (!$cur_application->user->employee) {
		$user = $cur_application->user;
	} else {
		// Generate a username
		$username = strtolower("{$cur_application->name_first}.{$cur_application->name_last}");
		$user = user::factory($username);
		while (isset($user->guid)) {
			$user = user::factory($tmp_username = $username.++$number);
			$user->username = $tmp_username;
		}
		// General applicant information
		$user->name_first = $cur_application->name_first;
		$user->name_middle = $cur_application->name_middle;
		$user->name_last = $cur_application->name_last;
		$user->name = $cur_application->name;
		$user->phone = $cur_application->phone;
		$user->email = $cur_application->email;
		$user->ssn = $cur_application->ssn;
		// Location
		$user->address_type = $cur_application->address_type;
		$user->address_1 = $cur_application->address_1;
		$user->address_2 = $cur_application->address_2;
		$user->city = $cur_application->city;
		$user->state = $cur_application->state;
		$user->zip = $cur_application->zip;
		$user->address_international = $cur_application->address_international;
		$user->save();
	}

	// Hire the employee.
	$user->employee = true;
	$user->hire_date = strtotime($_REQUEST['date']);
	$user->employment_history[] = array($user->hire_date, 'Hired');
	$cur_application->status = 'hired';

	if (!isset($user->timeclock->guid)) {
		$user->timeclock = com_hrm_timeclock::factory();
		$user->timeclock->user = $user;
		$user->timeclock->group = $user->group;
	}
	if ((array) $user->employee_attributes !== $user->employee_attributes)
		$user->employee_attributes = array();
	if ((array) $user->commissions !== $user->commissions)
		$user->commissions = array();
	if ( !isset($user->guid) || !$user->save() || !$cur_application->save())
		$failed_adds .= (empty($failed_adds) ? '' : ', ').$cur_application_id;
}
if (empty($failed_adds)) {
	pines_notice('Selected applicant(s) hired successfully.');
} else {
	pines_error('Could not hire the applicants with the following application IDs: '.$failed_adds);
}

pines_redirect(pines_url('com_hrm', 'employee/list'));

?>