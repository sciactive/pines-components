<?php
/**
 * Save changes to an employees timeclock.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/manageclock') )
	punt_user(null, pines_url('com_hrm', 'employee/timeclock/edit', array('id' => $_REQUEST['id'])));

$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
if (!isset($employee->guid)) {
	pines_error('Requested employee id is not accessible.');
	return;
}

$employee->timeclock->timeclock = array();

$clock = (array) json_decode($_REQUEST['clock']);

foreach($clock as $cur_entry) {
	$employee->timeclock->timeclock[] = array(
		'in' => (int) $cur_entry->in,
		'out' => (int) $cur_entry->out,
		'comments' => $cur_entry->comments
	);
}

if ($employee->timeclock->save() && $employee->save()) {
	pines_notice("Saved timeclock for {$employee->name}.");
} else {
	pines_error('Error saving timeclock. Do you have permission?');
}

redirect(pines_url('com_hrm', 'employee/timeclock/list'));

?>