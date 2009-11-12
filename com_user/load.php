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
 * @global com_user $config->user_manager
 */
$config->user_manager = new com_user;

/**
 * The ability manager.
 * @global abilities $config->ability_manager
 */
$config->ability_manager = new abilities;

/**
 * Filter entities being deleted for user permissions.
 *
 * @param array $array An array of an entity or guid.
 * @return array|bool An array of an entity or guid, or false on failure.
 */
function com_user_check_permissions_delete($array) {
	global $config;
	$entity = $array[0];
	if (is_int($entity))
	$entity = $config->entity_manager->get_entity($array[0]);
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
	global $config;
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
	global $config;
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
 * - Its UID is the user. (It is owned by the user.) (Check user AC.)
 * - Its parent is the user. (Check user AC.)
 * - Its GID is the user's primary group. (Check group AC.)
 * - Its parent is the user's primary group. (Check group AC.)
 * - Its GID is one of the user's secondary groups. (Check group AC.)
 * - Its parent is one of the user's secondary groups. (Check group AC.)
 * - Its GID is a child of one of the user's groups. (Check group AC.)
 * - Its parent is a child of one of the user's groups. (Check group AC.)
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
	if ($entity->guid == $_SESSION['user']->guid)
	return true;
	if ($entity->guid == $_SESSION['user']->gid)
	return true;

	// Load access control, since we need it now...
	$ac = (object) array('user' => 3, 'group' => 3, 'other' => 0);
	if (is_object($entity->ac))
	$ac = $entity->ac;

	if ($entity->uid == $_SESSION['user']->guid)
	return ($ac->user >= $type);
	if ($entity->parent == $_SESSION['user']->guid)
	return ($ac->user >= $type);
	if ($entity->gid == $_SESSION['user']->gid)
	return ($ac->group >= $type);
	if ($entity->parent == $_SESSION['user']->gid)
	return ($ac->group >= $type);
	if (in_array($entity->gid, $_SESSION['user']->groups))
	return ($ac->group >= $type);
	if (in_array($entity->parent, $_SESSION['user']->groups))
	return ($ac->group >= $type);
	if (in_array($entity->gid, $_SESSION['descendents']))
	return ($ac->group >= $type);
	if (in_array($entity->parent, $_SESSION['descendents']))
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
		!is_a($array[0], 'user') &&
		!is_a($array[0], 'group')
		) {
		
		$array[0]->uid = $_SESSION['user']->guid;
		$array[0]->gid = $_SESSION['user']->gid;
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

foreach (array('$config->entity_manager->get_entity', '$config->entity_manager->get_entities_by_data', '$config->entity_manager->get_entities_by_parent', '$config->entity_manager->get_entities_by_tags', '$config->entity_manager->get_entities_by_tags_exclusive', '$config->entity_manager->get_entities_by_tags_inclusive', '$config->entity_manager->get_entities_by_tags_mixed') as $cur_hook) {
	$config->hook->add_callback($cur_hook, 10, 'com_user_check_permissions_return');
}

$config->hook->add_callback('$config->entity_manager->save_entity', -100, 'com_user_add_access');
$config->hook->add_callback('$config->entity_manager->save_entity', -99, 'com_user_check_permissions_save');

foreach (array('$config->entity_manager->delete_entity', '$config->entity_manager->delete_entity_by_id') as $cur_hook) {
	$config->hook->add_callback($cur_hook, -99, 'com_user_check_permissions_delete');
}

?>