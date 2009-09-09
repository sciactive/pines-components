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
 * One of the following criteria is required for the entity to be returned:
 * - No user is logged in. (Should be managed with abilities.)
 * - The user has the "system/all" ability.
 * - The entity is the user.
 * - The entity is the user's primary group.
 * - The entity's parent is the user.
 * - The entity's parent is the user's primary group.
 * - The entity's group is the user's primary group.
 * - The entity's parent is one of the user's secondary groups.
 * - The entity's group is one of the user's secondary groups.
 * - The entity's parent is a child of one of the user's groups.
 * - The entity's group is a child of one of the user's groups.
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
            if ($cur_entity->guid == $_SESSION['user']->guid) {
                $pass = true;
                break;
            }
            if ($cur_entity->guid == $_SESSION['user']->gid) {
                $pass = true;
                break;
            }
            if ($cur_entity->parent == $_SESSION['user']->guid) {
                $pass = true;
                break;
            }
            if ($cur_entity->parent == $_SESSION['user']->gid) {
                $pass = true;
                break;
            }
            if ($cur_entity->gid == $_SESSION['user']->gid) {
                $pass = true;
                break;
            }
            if (in_array($cur_entity->parent, $_SESSION['user']->groups)) {
                $pass = true;
                break;
            }
            if (in_array($cur_entity->gid, $_SESSION['user']->groups)) {
                $pass = true;
                break;
            }
            if (in_array($cur_entity->parent, $_SESSION['descendents'])) {
                $pass = true;
                break;
            }
            if (in_array($cur_entity->gid, $_SESSION['descendents'])) {
                $pass = true;
                break;
            }
            break;
        }
        if ($pass) {
            $return[] = $cur_entity;
        }
    }
    return ($is_array ? array($return) : $return);
}

/**
 * Add the current user's GID to an entity.
 *
 * This occurs right before an entity is saved. This only occurs if the user has
 * a primary group, and the entity is not a user or group.
 *
 * @param array $array An array of either an entity or another array of entities.
 * @return array An array of either an entity or another array of entities.
 */
function com_user_add_group($array) {
    if (is_object($_SESSION['user']) && isset($_SESSION['user']->gid) && !is_a($array[0], 'user') && !is_a($array[0], 'group'))
        $array[0]->gid = $_SESSION['user']->gid;
    return $array;
}

foreach (array('$config->entity_manager->get_entity', '$config->entity_manager->get_entities_by_data', '$config->entity_manager->get_entities_by_parent', '$config->entity_manager->get_entities_by_tags', '$config->entity_manager->get_entities_by_tags_exclusive', '$config->entity_manager->get_entities_by_tags_inclusive', '$config->entity_manager->get_entities_by_tags_mixed') as $cur_hook) {
    $config->hook->add_callback($cur_hook, 1, 'com_user_check_permissions');
}

$config->hook->add_callback('$config->entity_manager->save_entity', -100, 'com_user_add_group');

?>