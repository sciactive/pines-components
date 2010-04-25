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

$errors = array();
$offset = 0;
// Grab all entities, 50 at a time, and replace uids/gids with users/groups.
do {
	$entities = $pines->entity_manager->get_entities(array('limit' => 50, 'offset' => $offset));
	// If we have run through all entities, we are done updating.
	foreach ($entities as &$cur_entity) {
		if ($cur_entity->has_tag('com_user'))
			continue;
		$changed = false;
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
		if ($changed && !$cur_entity->save())
			$errors[] = $entity->guid;
	}
	unset($cur_entity);
	$offset += 50;
} while (!empty($entities));

foreach ($errors as $cur_error)
	echo "Could not update Entity #{$cur_error}<br/>";

?>