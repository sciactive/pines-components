<?php
/**
 * Updates entities.
 *
 * This is only a temporary file. It will be removed shortly.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('system/all'))
	punt_user();

$module = new module('system', 'null', 'content');
$module->title = 'Entity Update';

$errors = array();
$offset = $count = $nochange = 0;
// Grab entities, 50 at a time.
do {
	$entities = $pines->entity_manager->get_entities(
		array('limit' => 50, 'offset' => $offset),
		array('&',
			'tag' => array('com_hrm', 'employee')
		)
	);
	// If we have run through all entities, we are done updating.
	foreach ($entities as &$cur_entity) {
		$changed = false;
		if (isset($cur_entity->user_account)) {
			$cur_entity->user_account->employee = true;
			$cur_entity->user_account->timeclock = $cur_entity->timeclock;
			$cur_entity->user_account->employee_attributes = $cur_entity->attributes;
			$changed = true;
		}
		if ($changed) {
			if ($cur_entity->user_account->save() && $cur_entity->delete())
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

$module->content("Updated $count entities. Found $nochange entities that don't have a user account, so couldn't be updated.");
if ($errors)
	$module->content('<br />Could not update the entities: '.implode(', ', $errors));

?>