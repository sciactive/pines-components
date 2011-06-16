<?php
/**
 * Get location contents.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/editsalesranking') )
	punt_user(null, pines_url('com_reports', 'salesrankings'));

$pines->page->override = true;

$group = group::factory((int) $_REQUEST['id']);
if (!isset($group->guid))
	return;

$descendents = (array) $group->get_descendents();

$users = (array) $group->get_users(true);

$json_data = array(
	$group->guid => array(
		'guid' => $group->guid,
		'type' => 'location',
		'name' => htmlspecialchars($group->name),
		'parent' => ($descendents || $users)
	)
);

// Add all the groups.
foreach ($descendents as $cur_group) {
	$json_data[$cur_group->guid] = array(
		'guid' => $cur_group->guid,
		'type' => 'location',
		'name' => htmlspecialchars($cur_group->name),
		'parent' => false,
		'parent_id' => (isset($cur_group->parent->guid) ? $cur_group->parent->guid : ''),
		'child' => isset($cur_group->parent->guid)
	);
}

// Mark all the parents.
foreach ($descendents as $cur_group) {
	if (isset($cur_group->parent->guid) && isset($json_data[$cur_group->parent->guid]))
		$json_data[$cur_group->parent->guid]['parent'] = true;
}

// Add all the users.
foreach ($users as $cur_user) {
	// Skip users who only have secondary groups.
	if (!isset($cur_user->group->guid) || !($cur_user->group->is($group) || $cur_user->group->in_array($descendents)))
		continue;
	// Skip users who aren't employees.
	if (!$cur_user->employee)
		continue;
	// Mark all the parents.
	$json_data[$cur_user->group->guid]['parent'] = true;
	// Add the user.
	$json_data[$cur_user->guid] = array(
		'guid' => $cur_user->guid,
		'type' => 'employee',
		'name' => htmlspecialchars($cur_user->name),
		'parent' => false,
		'parent_id' => $cur_user->group->guid,
		'child' => true
	);
}

$pines->page->override_doc(json_encode($json_data));

?>