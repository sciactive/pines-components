<?php
/**
 * Perform actions on groups, returning JSON.
 *
 * @package Pines
 * @subpackage com_jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

pines_session();
if (isset($_SESSION['user']->group)) {
	$my_group = clone $_SESSION['user']->group;
	$locations = $my_group->get_descendents();
	$my_group->parent = null;
	$locations[] = $my_group;
} else {
	$locations = $pines->user_manager->get_groups();
}

$pines->user_manager->group_sort($locations, 'name');

$groups_json_struct = $pines->com_jstree->entity_json_struct($locations);
$pines->page->override_doc(json_encode($groups_json_struct));

?>