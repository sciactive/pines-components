<?php
/**
 * Delete an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/deleteemployee') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'listemployees'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_employee) {
	$cur_entity = com_hrm_employee::factory((int) $cur_employee);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_employee;
}
if (empty($failed_deletes)) {
	pines_notice('Selected employee(s) deleted successfully.');
} else {
	pines_error('Could not delete employees with given IDs: '.$failed_deletes);
}

$pines->com_hrm->list_employees();
?>