<?php
/**
 * Default action of com_user.
 *
 * If the user is not logged in, a login page is provided. If he is, a list of
 * users will be printed.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() ) {
    $config->user_manager->print_login();
    return;
} else {
    if (empty($_SESSION['user']->default_component) || $_SESSION['user']->default_component == 'com_user') {
		if ( !gatekeeper('com_user/manage') ) {
			display_error('Your default component is set to com_user, but you don\'t have permission to use it.');
			return;
		}
        /**
         * If com_user is the default component, the default action is manageusers.
         */
        action('com_user', 'manageusers');
    } else {
        action($_SESSION['user']->default_component, 'default');
    }
}

?>