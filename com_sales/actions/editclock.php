<?php
/**
 * Edit an employees timeclock history.
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
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'editclock', $_REQUEST, false));
	return;
}

$user = user::factory((int) $_REQUEST['id']);

if (!gatekeeper('com_sales/clock', $user) && !is_array($user->com_sales->timeclock)) {
	display_notice("No timeclock data is stored for user [{$user->username}].");
}

$module = new module('com_sales', 'edit_clock', 'content');
$module->user = $user;

?>