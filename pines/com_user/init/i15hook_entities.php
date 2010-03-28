<?php
/**
 * Hook entity functions for access control.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Filter entities being deleted for user permissions.
 *
 * @param array $array An array of an entity or guid.
 * @return array|bool An array of an entity or guid, or false on failure.
 */
function com_user__check_permissions_delete($array) {
	global $pines;
	$entity = $array[0];
	if (is_int($entity))
		$entity = $pines->entity_manager->get_entity($array[0]);
	if (!is_object($entity))
		return false;
	// Test for permissions.
	if ($pines->user_manager->check_permissions($entity, 3)) {
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
function com_user__check_permissions_return($array) {
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
		if ($pines->user_manager->check_permissions($cur_entity, 1)) {
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
function com_user__check_permissions_save($array) {
	global $pines;
	$entity = $array[0];
	if (!is_object($entity))
		return false;
	// Test for permissions.
	if ($pines->user_manager->check_permissions($entity, 2)) {
		return $array;
	} else {
		return false;
	}
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
function com_user__add_access($array) {
	if (is_object($_SESSION['user']) &&
		!isset($array[0]->guid) &&
		!$array[0]->has_tag('com_user', 'user') &&
		!$array[0]->has_tag('com_user', 'group') ) {
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
	$pines->hook->add_callback($cur_hook, 10, 'com_user__check_permissions_return');
}

$pines->hook->add_callback('$pines->entity_manager->save_entity', -100, 'com_user__add_access');
$pines->hook->add_callback('$pines->entity_manager->save_entity', -99, 'com_user__check_permissions_save');

foreach (array('$pines->entity_manager->delete_entity', '$pines->entity_manager->delete_entity_by_id') as $cur_hook) {
	$pines->hook->add_callback($cur_hook, -99, 'com_user__check_permissions_delete');
}

?>