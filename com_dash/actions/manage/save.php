<?php
/**
 * Save changes to a dashboard.
 *
 * @package Components\dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_dash/manage') )
	punt_user(null, pines_url('com_dash', 'manage/list'));
$dashboard = com_dash_dashboard::factory((int) $_REQUEST['id']);
if (!isset($dashboard->guid)) {
	pines_error('Requested dashboard id is not accessible.');
	return;
}

// General
if ($_REQUEST['user'] == 'none')
	unset($dashboard->user);
else {
	$dashboard->user = user::factory((int) $_REQUEST['user']);
	if (!isset($dashboard->user->guid))
		unset($dashboard->user);
}
if ($_REQUEST['group'] == 'none')
	unset($dashboard->group);
else {
	$dashboard->group = group::factory((int) $_REQUEST['group']);
	if (!isset($dashboard->group->guid))
		unset($dashboard->group);
}
$dashboard->locked = ($_REQUEST['locked'] == 'ON');
$dashboard->ac->group = (abs((int) $_REQUEST['group_access']) % 4);
$dashboard->ac->other = (abs((int) $_REQUEST['other_access']) % 4);

// Advanced
// First get all users and groups.
$user_array = $pines->user_manager->get_users();
$group_array = $pines->user_manager->get_groups();

// Go through the users to see if this should be their dashboard.
foreach ($user_array as &$cur_user) {
	if (in_array((string) $cur_user->guid, $_REQUEST['users'])) {
		// The user was selected, so replace their dashboard if it's different.
		if (!$dashboard->is($cur_user->dashboard)) {
			$cur_user->dashboard = $dashboard;
			if (!$cur_user->save())
				pines_error("Couldn't set dashboard for user {$cur_user->name} [{$cur_user->username}].");
		}
	} else {
		// The user was not selected, so unset their dashboard if it's this one.
		if ($dashboard->is($cur_user->dashboard)) {
			unset($cur_user->dashboard);
			if (!$cur_user->save())
				pines_error("Couldn't unset dashboard for user {$cur_user->name} [{$cur_user->username}].");
		}
	}
}
unset($cur_user);
// Do the same, but for groups.
foreach ($group_array as &$cur_group) {
	if (in_array((string) $cur_group->guid, $_REQUEST['groups'])) {
		// The group was selected, so replace their dashboard if it's different.
		if (!$dashboard->is($cur_group->dashboard)) {
			$cur_group->dashboard = $dashboard;
			if (!$cur_group->save())
				pines_error("Couldn't set dashboard for group {$cur_group->name} [{$cur_group->groupname}].");
		}
	} else {
		// The group was not selected, so unset their dashboard if it's this one.
		if ($dashboard->is($cur_group->dashboard)) {
			unset($cur_group->dashboard);
			if (!$cur_group->save())
				pines_error("Couldn't unset dashboard for group {$cur_group->name} [{$cur_group->groupname}].");
		}
	}
}
unset($cur_group);

if ($dashboard->save())
	pines_notice('Saved dashboard for ['.$dashboard->user->name.']');
else
	pines_error('Error saving dashboard. Do you have permission?');

pines_redirect(pines_url('com_dash', 'manage/list'));

?>