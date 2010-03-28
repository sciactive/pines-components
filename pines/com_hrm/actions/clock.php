<?php
/**
 * Clock an employee in or out, returning their status in JSON.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/clock') && !gatekeeper('com_hrm/manageclock') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'clock', $_REQUEST, false));

$pines->page->override = true;

/**
 * Override the user manager's access control check.
 *
 * This allows a user with only read access to save timeclock entries.
 *
 * @param array $array The argument array.
 * @return array The altered arguments.
 */
function com_hrm__override_ac($array) {
	if ($array[0]->has_tag('com_hrm', 'employee'))
		$array[1] = 1;
	return $array;
}

if ($_REQUEST['id'] == 'self') {
	$employee = $pines->entity_manager->get_entity(array('ref' => array('user_account' => $_SESSION['user']), 'tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));
	$id_array = $pines->hook->add_callback('$pines->user_manager->check_permissions', -100, 'com_hrm__override_ac');
} else {
	if ( !gatekeeper('com_hrm/manageclock') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'clock', $_REQUEST, false));
	$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
}

if (is_null($employee->guid)) {
	$pines->page->override_doc('false');
	return;
}

if (!empty($employee->timeclock) && $employee->timeclock[count($employee->timeclock) - 1]['status'] == 'in') {
	$employee->timeclock[] = array('status' => 'out', 'time' => time());
} else {
	$employee->timeclock[] = array('status' => 'in', 'time' => time());
}

$entry = $employee->timeclock[count($employee->timeclock) - 1];
$entry['time'] = pines_date_format($entry['time']);
$pines->page->override_doc(json_encode(array($employee->save(), $entry)));

if ($id_array)
	$pines->hook->del_callback_by_id($id_array[0]);

?>