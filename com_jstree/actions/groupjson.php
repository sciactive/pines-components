<?php
/**
 * Perform actions on groups, returning JSON.
 *
 * @package Components\jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
header('Content-Type: application/json');

if ($_REQUEST['all'] == 'true') {
	$locations = $pines->user_manager->get_groups();
} elseif ($_REQUEST['primaries'] == 'true') {
	$my_group = group::factory((int) $pines->config->com_user->highest_primary);
	if (!isset($my_group->guid))
		$locations = $pines->user_manager->get_groups();
	else {
		$locations = $my_group->get_children();
		$descendants = array();
		foreach ($locations as &$cur_location) {
			$cur_location->parent = null;
			$cur_descendants = $cur_location->get_descendants();
			foreach ($cur_descendants as $cur_descendant) {
				if (!$cur_descendant->in_array($descendants) && !$cur_descendant->in_array($locations))
					$descendants[] = $cur_descendant;
			}
		}
		unset($cur_location);
		$locations = $locations + $descendants;
	}
} elseif (isset($_SESSION['user']->group)) {
	$my_group = clone $_SESSION['user']->group;
	$locations = $my_group->get_descendants();
	$my_group->parent = null;
	$locations[] = $my_group;
} else {
	$locations = $pines->user_manager->get_groups();
}

$pines->user_manager->group_sort($locations, 'name');

$groups_json_struct = $pines->com_jstree->entity_json_struct($locations);
$pines->page->override_doc(json_encode($groups_json_struct));

?>