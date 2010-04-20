<?php
/**
 * Default action of com_user.
 *
 * If the user is not logged in, a login page is provided. If he is, a list of
 * users will be printed.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() ) {
	$pines->user_manager->print_login();
	return;
} else {
	if (empty($_SESSION['user']->default_component) || $_SESSION['user']->default_component == 'com_user') {
		if ( !gatekeeper('com_user/listusers') ) {
			pines_error('Your default component is set to com_user, but you don\'t have permission to use it.');
			return;
		}
		// If this is the default component.
		action('com_user', 'listusers');
	} else {
		// If the user has another component set to default.
		action($_SESSION['user']->default_component, 'default');
	}
}

?>