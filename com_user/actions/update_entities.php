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
		if ($cur_entity->has_tag('com_customer', 'customer')) {
			if (!$cur_entity->has_tag('com_user', 'user')) {
				$cur_entity->add_tag('com_user', 'user');
				$changed = true;
			}
			// Disable customers by default.
			if (!isset($cur_entity->enabled)) {
				$cur_entity->enabled = false;
				$changed = true;
			}
			if (!isset($cur_entity->abilities)) {
				$cur_entity->abilities = array();
				$changed = true;
			}
			if (!isset($cur_entity->inherit_abilities)) {
				$cur_entity->inherit_abilities = true;
				$changed = true;
			}
			if (!isset($cur_entity->username)) {
				$cur_entity->username = "user{$cur_entity->guid}";
				$changed = true;
			}
			if (!isset($cur_entity->timezone)) {
				$cur_entity->timezone = '';
				$changed = true;
			}
			if (!isset($cur_entity->salt)) {
				$cur_entity->salt = md5(rand());
				$cur_entity->password = md5($cur_entity->password.$cur_entity->salt);
				$changed = true;
			}
			if (empty($cur_entity->group) || empty($cur_entity->groups)) {
				// Load default groups.
				global $pines;
				$group = $pines->entity_manager->get_entity(
						array('class' => group),
						array('&',
							'data' => array('default_customer_primary', true),
							'tag' => array('com_user', 'group')
						)
					);
				if (isset($group->guid)) {
					$cur_entity->group = $group;
					$changed = true;
				}
				$groups = $pines->entity_manager->get_entities(
						array('class' => group),
						array('&',
							'data' => array('default_customer_secondary', true),
							'tag' => array('com_user', 'group')
						)
					);
				if ($groups) {
					$cur_entity->groups = $groups;
					$changed = true;
				}
			}
		}
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
			// Break apart names.
			if ($cur_entity->has_tag('user') && !isset($cur_entity->name_first)) {
				$cur_entity->name_first = preg_replace('/^(\w+) ?.*/', '$1', $cur_entity->name);
				if (preg_match('/^\w+ (\w+) \w+$/', $cur_entity->name))
					$cur_entity->name_middle = preg_replace('/^\w+ (\w+) \w+$/', '$1', $cur_entity->name);
				else
					$cur_entity->name_middle = '';
				if (preg_match('/.* (\w+)$/', $cur_entity->name))
					$cur_entity->name_last = preg_replace('/.* (\w+)$/', '$1', $cur_entity->name);
				else
					$cur_entity->name_last = '';
				$cur_entity->name = $cur_entity->name_first.(!empty($cur_entity->name_middle) ? ' '.$cur_entity->name_middle : '').(!empty($cur_entity->name_last) ? ' '.$cur_entity->name_last : '');

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