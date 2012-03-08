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

// TODO: Remove this test code.
if ($_REQUEST['reset'] == 'true') {
	pines_session('write');
	if (isset($_SESSION['user']->dashboard->guid))
		$_SESSION['user']->dashboard->delete();
	unset($_SESSION['user']->dashboard);
	$_SESSION['user']->save();
	pines_session('close');
}
// End remove.

if (!empty($_REQUEST['id']) && gatekeeper('com_dash/manage'))
	$dashboard = com_dash_dashboard::factory((int) $_REQUEST['id']);
else {
	if (!isset($_SESSION['user']->dashboard->guid)) {
		pines_session('write');
		$_SESSION['user']->dashboard = com_dash_dashboard::factory();
		if (!$_SESSION['user']->dashboard->save() || !$_SESSION['user']->save()) {
			pines_session('close');
			pines_error('Couldn\'t load your dashboard.');
			return;
		}
		pines_session('close');
	}
	$dashboard =& $_SESSION['user']->dashboard;
}
if (!isset($dashboard->guid))
	return 'error_404';

if (!$dashboard->print_dashboard($_REQUEST['tab'], gatekeeper('com_dash/editdash')))
	pines_error('Couldn\'t load your dashboard.');

?>