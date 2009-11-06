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
 * Filter the entities returned by the entity manager for correct user
 * permissions.
 *
 * This will check the variable "ac" (Access Control) of the entity. It
 * should be an object that contains the following variables:
 * - user
 * - group
 * - other
 *
 * The variable "user" refers to the entity's owner, "group" refers to the
 * entity's group, and "other" refers to any user who doesn't fit the others.
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
 * whether the entity is returned:
 * - No user is logged in. (Always returned, should be managed with abilities.)
 * - The entity has no uid and no gid. (Always returned.)
 * - The user has the "system/all" ability. (Always returned.)
 * - The entity is the user. (Always returned.)
 * - The entity is the user's primary group. (Always returned.)
 * - The entity's UID is the user. (It is owned by the user.) (Check user AC.)
 * - The entity's parent is the user. (Check user AC.)
 * - The entity's parent is the user's primary group. (Check group AC.)
 * - The entity's group is the user's primary group. (Check group AC.)
 * - The entity's parent is one of the user's secondary groups. (Check group AC.)
 * - The entity's group is one of the user's secondary groups. (Check group AC.)
 * - The entity's parent is a child of one of the user's groups. (Check group AC.)
 * - The entity's group is a child of one of the user's groups. (Check group AC.)
 * - None of the above. (Check other AC.)
 *
 * @param array $array An array of either an entity or another array of entities.
 * @return array An array of either an entity or another array of entities.
 */
function com_user_check_permissions($array) {
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
	$ac = (object) array('user' => 3, 'group' => 3, 'other' => 0);
	if (is_object($cur_entity->ac)) {
	    $ac = $cur_entity->ac;
	}
        $pass = false;
        // Test for permissions.
        while (true) {
            if (!is_object($_SESSION['user'])) {
                $pass = true;
                break;
            }
            if (function_exists('gatekeeper')) {
                if (gatekeeper('system/all')) {
                    $pass = true;
                    break;
                }
            }
            if (!isset($cur_entity->uid) && !isset($cur_entity->gid)) {
                $pass = true;
                break;
            }
            if ($cur_entity->guid == $_SESSION['user']->guid) {
                $pass = true;
                break;
            }
            if ($cur_entity->guid == $_SESSION['user']->gid) {
                $pass = true;
                break;
            }
            if ($cur_entity->uid == $_SESSION['user']->guid) {
		if ($ac->user >= 1)
		    $pass = true;
                break;
            }
            if ($cur_entity->parent == $_SESSION['user']->guid) {
		if ($ac->user >= 1)
		    $pass = true;
                break;
            }
            if ($cur_entity->parent == $_SESSION['user']->gid) {
		if ($ac->group >= 1)
		    $pass = true;
                break;
            }
            if ($cur_entity->gid == $_SESSION['user']->gid) {
		if ($ac->group >= 1)
		    $pass = true;
                break;
            }
            if (in_array($cur_entity->parent, $_SESSION['user']->groups)) {
		if ($ac->group >= 1)
		    $pass = true;
                break;
            }
            if (in_array($cur_entity->gid, $_SESSION['user']->groups)) {
		if ($ac->group >= 1)
		    $pass = true;
                break;
            }
            if (in_array($cur_entity->parent, $_SESSION['descendents'])) {
		if ($ac->group >= 1)
		    $pass = true;
                break;
            }
            if (in_array($cur_entity->gid, $_SESSION['descendents'])) {
		if ($ac->group >= 1)
		    $pass = true;
                break;
            }
	    if ($ac->other >= 1)
		$pass = true;
            break;
        }
        if ($pass) {
            $return[] = $cur_entity;
        }
    }
    return ($is_array ? array($return) : $return);
}

/**
 * Add the current user's GID to a new entity.
 *
 * This occurs right before an entity is saved. It only alters the entity if:
 * - There is a user logged in.
 * - The user has a primary group.
 * - The entity is new (doesn't have a GUID.)
 * - The entity is not a user or group.
 *
 * If you want a new entity to have a different UID and/or GID than the current
 * user, you must first save it to the database, then change the UID/GID, then
 * save it again.
 *
 * @param array $array An array of either an entity or another array of entities.
 * @return array An array of either an entity or another array of entities.
 */
function com_user_add_group($array) {
    if (is_object($_SESSION['user']) &&
        isset($_SESSION['user']->gid) &&
        is_null($array[0]->guid) &&
        !is_a($array[0], 'user') &&
        !is_a($array[0], 'group')
        ) {
        
        $array[0]->uid = $_SESSION['user']->guid;
        $array[0]->gid = $_SESSION['user']->gid;
    }
    return $array;
}

foreach (array('$config->entity_manager->get_entity', '$config->entity_manager->get_entities_by_data', '$config->entity_manager->get_entities_by_parent', '$config->entity_manager->get_entities_by_tags', '$config->entity_manager->get_entities_by_tags_exclusive', '$config->entity_manager->get_entities_by_tags_inclusive', '$config->entity_manager->get_entities_by_tags_mixed') as $cur_hook) {
    $config->hook->add_callback($cur_hook, 1, 'com_user_check_permissions');
}

$config->hook->add_callback('$config->entity_manager->save_entity', -100, 'com_user_add_group');

?>