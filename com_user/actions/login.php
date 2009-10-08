<?php
/**
 * Log a user into the system.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( $_REQUEST['username'] ) {
	if ( gatekeeper() ) {
		display_notice('Already logged in!');
		return;
	}
	if ( $id = $config->user_manager->authenticate($_REQUEST['username'], $_REQUEST['password']) ) {
		$config->user_manager->login($id);
		if ( !empty($_REQUEST['url']) ) {
			header('Location: '.urldecode($_REQUEST['url']));
			exit;
		} else {
            // Load the user's default component.
            action($config->default_component, 'default');
		}
	} else {
		display_error("Username and password not correct!");
        $config->user_manager->print_login();
	}
} else {
	$config->user_manager->print_login();
}

?>