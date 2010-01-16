<?php
/**
 * Edit a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_user/edituser') && (!gatekeeper('com_user/self') || ($_REQUEST['id'] != $_SESSION['user_id'])) )
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_user', 'listusers', null, false));
	$user = user::factory((int) $_REQUEST['id']);
} else {
	if ( !gatekeeper('com_user/newuser') )
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_user', 'listusers', null, false));
	$user = user::factory();
}

$user->print_form();

?>