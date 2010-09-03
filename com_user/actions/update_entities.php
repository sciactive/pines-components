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

// Load default group.
$group = $pines->entity_manager->get_entity(
		array('class' => group),
		array('&',
			'data' => array('default_customer_primary', true),
			'tag' => array('com_user', 'group')
		)
	);
// Load default groups.
//$groups = $pines->entity_manager->get_entities(
//		array('class' => group),
//		array('&',
//			'data' => array('default_customer_secondary', true),
//			'tag' => array('com_user', 'group')
//		)
//	);

$errors = array();
$offset = $count = $nochange = 0;
// Grab all entities, 100 at a time, and update them.
do {
	$entities = $pines->entity_manager->get_entities(
			array('limit' => 100, 'offset' => $offset),
			array('&',
				'tag' => array('com_customer', 'customer')
			)
		);
	// If we have run through all entities, we are done updating.
	foreach ($entities as &$cur_entity) {
		$changed = false;
		if (!$cur_entity->has_tag('com_user', 'user')) {
			$cur_entity->add_tag('com_user', 'user');
			$changed = true;
		}
		if (isset($group->guid) && !$group->is($cur_entity->group)) {
			$cur_entity->group = $group;
			$changed = true;
		}
//		if ($groups && empty($cur_entity->groups)) {
//			$cur_entity->groups = $groups;
//			$changed = true;
//		}
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