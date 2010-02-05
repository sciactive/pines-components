<?php
/**
 * com_user's loader.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The user manager.
 * @global com_user $pines->user_manager
 */
$pines->user_manager = 'com_user';

/**
 * The ability manager.
 * @global abilities $pines->ability_manager
 */
$pines->ability_manager = 'abilities';

/**
 * Filter entities being deleted for user permissions.
 *
 * @param array $array An array of an entity or guid.
 * @return array|bool An array of an entity or guid, or false on failure.
 */
function com_user_check_permissions_delete($array) {
	global $pines;
	$entity = $array[0];
	if (is_int($entity))
		$entity = $pines->entity_manager->get_entity($array[0]);
	if (!is_object($entity))
		return false;
	// Test for permissions.
	if (com_user_check_permissions($entity, 3)) {
		return $array;
	} else {
		return false;
	}
}

/**
 * Filter entities being returned for user permissions.
 *
 * @param array $array An array of either an entity or another array of entities.
 * @return array An array of either an entity or another array of entities.
 */
function com_user_check_permissions_return($array) {
	global $pines;
	if (is_array($array[0])) {
		$is_array = true;
		$entities = $array[0];
	} else {
		$is_array = false;
		$entities = $array;
	}
	$return = array();
	foreach ($entities as $cur_entity) {
	// Test for permissions.
		if (com_user_check_permissions($cur_entity, 1)) {
			$return[] = $cur_entity;
		}
	}
	return ($is_array ? array($return) : $return);
}

/**
 * Filter entities being saved for user permissions.
 *
 * @param array $array An array of an entity.
 * @return array|bool An array of an entity or false on failure.
 */
function com_user_check_permissions_save($array) {
	global $pines;
	$entity = $array[0];
	if (!is_object($entity))
		return false;
	// Test for permissions.
	if (com_user_check_permissions($entity, 2)) {
		return $array;
	} else {
		return false;
	}
}

/**
 * Check an entity's permissions for the currently logged in user.
 *
 * This will check the variable "ac" (Access Control) of the entity. It should
 * be an object that contains the following variables:
 * - user
 * - group
 * - other
 *
 * The variable "user" refers to the entity's owner, "group" refers to all users
 * in the entity's group and all ancestor groups, and "other" refers to any user
 * who doesn't fit these descriptions.
 *
 * Each variable should be either 0, 1, 2, or 3. If it is 0, the user has no
 * access to the entity. If it is 1, the user has read access to the entity. If
 * it is 2, the user has read and write access to the entity. If it is 3, the
 * user has read, write, and delete access to the entity.
 *
 * "ac" defaults to:
 * - user = 3
 * - group = 3
 * - other = 0
 *
 * The following conditions will result in different checks, which determine
 * whether the check passes:
 * - No user is logged in. (Always returned, should be managed with abilities.)
 * - The entity has no uid and no gid. (Always returned.)
 * - The user has the "system/all" ability. (Always returned.)
 * - The entity is the user. (Always returned.)
 * - It is the user's primary group. (Always returned.)
 * - The entity is a user or group. (Always returned.)
 * - Its UID is the user. (It is owned by the user.) (Check user AC.)
 * - Its GID is the user's primary group. (Check group AC.)
 * - Its GID is one of the user's secondary groups. (Check group AC.)
 * - Its GID is a child of one of the user's groups. (Check group AC.)
 * - None of the above. (Check other AC.)
 *
 * @param object &$entity The entity to check.
 * @param int $type The lowest level of permission to consider a pass. 1 is read, 2 is write, 3 is delete.
 * @return bool Whether the current user has at least $type permission for the entity.
 */
function com_user_check_permissions(&$entity, $type = 1) {
	if (!is_object($_SESSION['user']))
		return true;
	if (function_exists('gatekeeper')) {
		if (gatekeeper('system/all'))
			return true;
	}
	if (!isset($entity->uid) && !isset($entity->gid))
		return true;
	if ($entity->is($_SESSION['user']))
		return true;
	if ($entity->is($_SESSION['user']->group))
		return true;
	if ($entity->has_tag('com_user', 'user') || $entity->has_tag('com_user', 'group'))
		return true;

	// Load access control, since we need it now...
	$ac = (object) array('user' => 3, 'group' => 3, 'other' => 0);
	if (is_object($entity->ac))
		$ac = $entity->ac;

	if ($entity->uid == $_SESSION['user']->guid)
		return ($ac->user >= $type);
	if ($entity->gid == $_SESSION['user']->group->guid)
		return ($ac->group >= $type);
	if (group::factory((int) $entity->gid)->in_array($_SESSION['user']->groups))
		return ($ac->group >= $type);
	if (group::factory((int) $entity->gid)->in_array($_SESSION['descendents']))
		return ($ac->group >= $type);
	return ($ac->other >= $type);
}

/**
 * Add the current user's UID, GID, and access control to a new entity.
 *
 * This occurs right before an entity is saved. It only alters the entity if:
 * - There is a user logged in.
 * - The entity is new (doesn't have a GUID.)
 * - The entity is not a user or group.
 *
 * If you want a new entity to have a different UID and/or GID than the current
 * user, you must first save it to the database, then change the UID/GID, then
 * save it again.
 *
 * Default access control is
 * - user = 3
 * - group = 3
 * - other = 0
 *
 * @param array $array An array of either an entity or another array of entities.
 * @return array An array of either an entity or another array of entities.
 */
function com_user_add_access($array) {
	if (is_object($_SESSION['user']) &&
		is_null($array[0]->guid) &&
		!$array[0]->has_tag('com_user', 'user') &&
		!$array[0]->has_tag('com_user', 'group')
	) {

		$array[0]->uid = $_SESSION['user']->guid;
		$array[0]->gid = $_SESSION['user']->group->guid;
		if (!is_object($array[0]->ac))
			$array[0]->ac = (object) array();
		if (!isset($array[0]->ac->user))
			$array[0]->ac->user = 3;
		if (!isset($array[0]->ac->group))
			$array[0]->ac->group = 3;
		if (!isset($array[0]->ac->other))
			$array[0]->ac->other = 0;
	}
	return $array;
}

foreach (array('$pines->entity_manager->get_entity', '$pines->entity_manager->get_entities') as $cur_hook) {
	$pines->hook->add_callback($cur_hook, 10, 'com_user_check_permissions_return');
}

$pines->hook->add_callback('$pines->entity_manager->save_entity', -100, 'com_user_add_access');
$pines->hook->add_callback('$pines->entity_manager->save_entity', -99, 'com_user_check_permissions_save');

foreach (array('$pines->entity_manager->delete_entity', '$pines->entity_manager->delete_entity_by_id') as $cur_hook) {
	$pines->hook->add_callback($cur_hook, -99, 'com_user_check_permissions_delete');
}

?>