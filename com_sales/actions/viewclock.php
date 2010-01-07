<?php
/**
 * View an employees timeclock history.
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
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'viewclock', $_REQUEST, false));
	return;
}

$user = user::factory((int) $_REQUEST['id']);

if (!gatekeeper('com_sales/clock', $user) && !is_array($user->com_sales->timeclock)) {
	display_notice("No timeclock data is stored for user [{$user->username}].");
}

$pgrid = new module('system', 'pgrid.default', 'head');
$pgrid->icons = true;

$module = new module('com_sales', 'view_clock', 'content');
$module->user = $user;
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/view_clock'];

?>