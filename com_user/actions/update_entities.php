<?php
/**
 * Update entities.
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
$module->title = 'Entity Update';

$errors = array();
$offset = $count = $nochange = 0;
// Grab all entities, 100 at a time, and update them.
do {
	$entities = $pines->entity_manager->get_entities(
			array('limit' => 100, 'offset' => $offset),
			array('&',
				'tag' => array('com_user')
			),
			array('|',
				'tag' => array('user', 'group')
			)
		);
	// If we have run through all entities, we are done updating.
	foreach ($entities as &$cur_entity) {
		$changed = false;
		if (isset($cur_entity->enabled)) {
			if ($cur_entity->enabled)
				$cur_entity->add_tag('enabled');
			else
				$cur_entity->remove_tag('enabled');
			unset($cur_entity->enabled);
			$changed = true;
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
} while ($entities);

$module->content("Updated $count entities. Found $nochange entities that didn't need to be updated.");
if ($errors)
	$module->content('<br />Could not updated the entities: '.implode(', ', $errors));

?>