<?php
/**
 * Clock an employee in or out, returning their status in JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/clock') && !gatekeeper('com_sales/manageclock') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'clock', $_REQUEST, false));
	return;
}

$page->override = true;

if ($_REQUEST['id'] == 'self') {
	$user = $_SESSION['user'];
} else {
	if ( !gatekeeper('com_sales/manageclock') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'clock', $_REQUEST, false));
		return;
	}
	$user = $config->user_manager->get_user((int) $_REQUEST['id']);
}

if (is_null($user)) {
	$page->override_doc('false');
	return;
}

if (!is_a($user->com_sales, 'com_sales_employee_data'))
	$user->com_sales = new com_sales_employee_data;

if (!is_array($user->com_sales->timeclock))
	$user->com_sales->timeclock = array();

if (!empty($user->com_sales->timeclock) && $user->com_sales->timeclock[count($user->com_sales->timeclock) - 1]['status'] == 'in') {
	$user->com_sales->timeclock[] = array('status' => 'out', 'time' => time());
} else {
	$user->com_sales->timeclock[] = array('status' => 'in', 'time' => time());
}

$entry = $user->com_sales->timeclock[count($user->com_sales->timeclock) - 1]['time'];
$page->override_doc(json_encode(array($user->com_sales->save() && $user->save(), pines_date_format($entry))));

?>