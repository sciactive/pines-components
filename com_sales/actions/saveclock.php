<?php
/**
 * Save changes to an employees timeclock.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/manageclock') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'editclock', array('id' => $_REQUEST['id']), false));
	return;
}

$user = user::factory((int) $_REQUEST['id']);
if (is_null($user->guid)) {
	display_error('Requested user id is not accessible');
	return;
}

if (!isset($user->com_sales))
	$user->com_sales = com_sales_employee_data::factory();

$user->com_sales->timeclock = array();

$clock = json_decode($_REQUEST['clock']);
if (!is_array($clock))
	$clock = array();

foreach($clock as $cur_entry) {
	$user->com_sales->timeclock[] = array(
		'time' => (int) $cur_entry->time,
		'status' => ($cur_entry->status == 'out' ? 'out' : 'in')
	);
}

if ($user->com_sales->save() && $user->save()) {
	display_notice("Saved timeclock for {$user->name} [{$user->username}]");
} else {
	display_error('Error saving timeclock. Do you have permission?');
}

action('com_sales', 'manageclock');
?>