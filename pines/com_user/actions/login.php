<?php
/**
 * Log a user into the system.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['username']) && !empty($_REQUEST['username']) ) {
	if ( gatekeeper() && $_REQUEST['username'] == $_SESSION['user']->username ) {
		display_notice('Already logged in!');
		return;
	}
	if ( $id = $pines->user_manager->authenticate($_REQUEST['username'], $_REQUEST['password']) ) {
		$pines->user_manager->login($id);
		if ( !empty($_REQUEST['url']) ) {
			header('Location: '.urldecode($_REQUEST['url']));
			exit;
		} else {
			// Load the user's default component.
			action($pines->config->default_component, 'default');
		}
	} else {
		display_notice("Username and password not correct!");
		$pines->user_manager->print_login();
	}
} else {
	$pines->user_manager->print_login();
}

?>