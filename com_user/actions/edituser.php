<?php
/**
 * Edit a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	pines_session();
	if ( !gatekeeper('com_user/edituser') && (!gatekeeper('com_user/self') || ($_REQUEST['id'] != $_SESSION['user_id'])) )
		punt_user(null, pines_url('com_user', 'listusers'));
} else {
	if ( !gatekeeper('com_user/newuser') )
		punt_user(null, pines_url('com_user', 'listusers'));
}

$user = user::factory((int) $_REQUEST['id']);
$user->print_form();

?>