<?php
/**
 * Remove an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/removeemployee') )
	punt_user(null, pines_url('com_hrm', 'employee/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_employee) {
	$cur_user = user::factory((int) $cur_employee);
	unset($cur_user->employee);
	if ( !isset($cur_user->guid) || !$cur_user->save() )
		$failed_removes .= (empty($failed_removes) ? '' : ', ').$cur_employee;
}
if (empty($failed_removes)) {
	pines_notice('Selected employee(s) removed successfully.');
} else {
	pines_error('Could not remove employees with given IDs: '.$failed_removes);
}

pines_redirect(pines_url('com_hrm', 'employee/list'));

?>