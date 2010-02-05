<?php
/**
 * Clock an employee in or out, returning their status in JSON.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/clock') && !gatekeeper('com_hrm/manageclock') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'clock', $_REQUEST, false));

$config->page->override = true;

if ($_REQUEST['id'] == 'self') {
	$employee = $config->entity_manager->get_entity(array('ref' => array('user_account' => $_SESSION['user']), 'tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));
} else {
	if ( !gatekeeper('com_hrm/manageclock') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'clock', $_REQUEST, false));
	$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
}

if (is_null($employee->guid)) {
	$config->page->override_doc('false');
	return;
}

if (!empty($employee->timeclock) && $employee->timeclock[count($employee->timeclock) - 1]['status'] == 'in') {
	$employee->timeclock[] = array('status' => 'out', 'time' => time());
} else {
	$employee->timeclock[] = array('status' => 'in', 'time' => time());
}

$entry = $employee->timeclock[count($employee->timeclock) - 1];
$entry['time'] = pines_date_format($entry['time']);
$config->page->override_doc(json_encode(array($employee->save(), $entry)));

?>