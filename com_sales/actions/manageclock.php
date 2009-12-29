<?php
/**
 * Manage the timeclock.
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
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'manageclock', null, false));
	return;
}

$users = $config->user_manager->get_users();

foreach($users as $key => &$cur_user) {
	if (!gatekeeper('com_sales/clock', $cur_user))
		unset($users[$key]);
}

$pgrid = new module('system', 'pgrid.default', 'head');
$pgrid->icons = true;

$module = new module('com_sales', 'manage_clock', 'content');
$module->users = $users;
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/manage_clock'];

?>