<?php
/**
 * Display dashboard.
 *
 * @package Pines
 * @subpackage com_dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_dash/dash') ) {
	if ($pines->request_option == '' && $pines->request_action == '' && !gatekeeper()) {
		// This is the default component, and the user isn't logged in, so
		// instead of just dumping the user with a nasty message, print a nice
		// login form.
		$pines->user_manager->print_login();
		return;
	} else
		punt_user(null, pines_url('com_dash'));
}

if (!$pines->com_dash->show_dash($_REQUEST['tab']))
	pines_error('Couldn\'t load your dashboard.');

?>