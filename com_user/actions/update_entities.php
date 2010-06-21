<?php
/**
 * Updates all entity uids/gids to user/group entity references.
 *
 * This is only a temporary file. It will be removed shortly.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('system/all'))
	punt_user('You don\'t have necessary permission.');

$module = new module('system', 'null', 'content');
$module->title = 'Entity Reference Update';

$errors = array();
$offset = $count = $nochange = 0;
// Grab all entities, 50 at a time, and replace uids/gids with users/groups.
do {
	$entities = $pines->entity_manager->get_entities(array('limit' => 50, 'offset' => $offset));
	// If we have run through all entities, we are done updating.
	foreach ($entities as &$cur_entity) {
		$changed = false;
		if ($cur_entity->has_tag('com_user')) {
			// Enable users/groups by default.
			if (!isset($cur_entity->enabled)) {
				$cur_entity->enabled = true;
				$changed = true;
			}
			// Add an addresses array.
			if ($cur_entity->has_tag('user') && !isset($cur_entity->addresses)) {
				$cur_entity->addresses = array();
				$changed = true;
			}
		} else {
			// Replace UIDs
			if (isset($cur_entity->uid)) {
				$cur_entity->user = user::factory((int) $cur_entity->uid);
				unset($cur_entity->uid);
				$changed = true;
			}
			// Replace GIDs
			if (isset($cur_entity->gid)) {
				$cur_entity->group = group::factory((int) $cur_entity->gid);
				unset($cur_entity->gid);
				$changed = true;
			}
		}
		if ($changed) {
			if ($cur_entity->save())
				$count++;
			else
				$errors[] = $entity->guid;
		} else {
			$nochange++;
		}
	}
	unset($cur_entity);
	$offset += 50;
} while (!empty($entities));

$module->content("Updated $count entities. Found $nochange entities that didn't need to be updated.");
if ($errors)
	$module->content('<br />Could not updated the entities: '.implode(', ', $errors));

?>