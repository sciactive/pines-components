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

if (!empty($_REQUEST['id']) && gatekeeper('com_dash/manage')) {
	$dashboard = com_dash_dashboard::factory((int) $_REQUEST['id']);
	$editable = true;
} else {
	if (!isset($_SESSION['user']->dashboard->guid)) {
		pines_session('write');
		$_SESSION['user']->refresh();
		if (!isset($_SESSION['user']->dashboard->guid)) {
			while (true) { // Use a while so we can break out.
				// Check the primary group for a dashboard.
				if (isset($_SESSION['user']->group->dashboard->guid)) {
					$_SESSION['user']->dashboard = $_SESSION['user']->group->dashboard;
					if (!$_SESSION['user']->save()) {
						pines_session('close');
						pines_error('Couldn\'t load your dashboard.');
						return;
					}
					break;
				}
				// Check the secondary groups for a dashboard.
				foreach ($_SESSION['user']->groups as $cur_group) {
					if (isset($cur_group->dashboard->guid)) {
						$_SESSION['user']->dashboard = $cur_group->dashboard;
						if (!$_SESSION['user']->save()) {
							pines_session('close');
							pines_error('Couldn\'t load your dashboard.');
							return;
						}
						break 2;
					}
				}
				// Check the primary group's ancestors for a dashboard.
				$parent = $_SESSION['user']->group->parent;
				while ($parent->guid) {
					if (isset($parent->dashboard->guid)) {
						$_SESSION['user']->dashboard = $parent->dashboard;
						if (!$_SESSION['user']->save()) {
							pines_session('close');
							pines_error('Couldn\'t load your dashboard.');
							return;
						}
						break 2;
					}
					$parent = $parent->parent;
				}
				// Couldn't find a dashboard, so make a new one.
				$_SESSION['user']->dashboard = com_dash_dashboard::factory();
				if (!$_SESSION['user']->dashboard->save() || !$_SESSION['user']->save()) {
					pines_session('close');
					pines_error('Couldn\'t load your dashboard.');
					return;
				}
				break;
			}
		}
		pines_session('close');
	}
	$dashboard =& $_SESSION['user']->dashboard;
	$editable = !$dashboard->locked && gatekeeper('com_dash/editdash');
}
if (!isset($dashboard->guid))
	return 'error_404';

if (!$dashboard->print_dashboard($_REQUEST['tab'], $editable))
	pines_error('Couldn\'t load your dashboard.');

?>