<?php
/**
 * com_user class.
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
 * com_user main class.
 *
 * Provides an entity manager based user and group manager.
 *
 * @package Pines
 * @subpackage com_user
 */
class com_user extends component {
	/**
	 * Gatekeeper ability cache.
	 *
	 * Gatekeeper will cache user's abilities that it calculates, so it can
	 * check faster if that user has been checked before.
	 *
	 * @access private
	 * @var array $gatekeeper_cache
	 */
	private $gatekeeper_cache = array();
	/**
	 * Groupname cache.
	 *
	 * @access private
	 * @var array $groupname_cache
	 */
	private $groupname_cache = array();
	/**
	 * Username cache.
	 *
	 * @access private
	 * @var array $username_cache
	 */
	private $username_cache = array();

	/**
	 * Authenticate a user's credentials.
	 *
	 * This function will not log a user into the system. It will only check
	 * that the information provided are valid login credentials.
	 *
	 * @param string $username The username of the user.
	 * @param string $password The password of the user.
	 * @return int|bool The user's GUID on success, false on failure.
	 */
	function authenticate($username, $password) {
		$entity = user::factory($username);
		if (is_null($entity->guid))
			return false;
		if ( $entity->check_password($password) )
			return $entity->guid;
		return false;
	}

	/**
	 * Check an entity's permissions for the currently logged in user.
	 *
	 * This will check the variable "ac" (Access Control) of the entity. It should
	 * be an object that contains the following properties:
	 *
	 * - user
	 * - group
	 * - other
	 *
	 * The property "user" refers to the entity's owner, "group" refers to all users
	 * in the entity's group and all ancestor groups, and "other" refers to any user
	 * who doesn't fit these descriptions.
	 *
	 * Each variable should be either 0, 1, 2, or 3. If it is 0, the user has no
	 * access to the entity. If it is 1, the user has read access to the entity. If
	 * it is 2, the user has read and write access to the entity. If it is 3, the
	 * user has read, write, and delete access to the entity.
	 *
	 * "ac" defaults to:
	 *
	 * - user = 3
	 * - group = 3
	 * - other = 0
	 *
	 * The following conditions will result in different checks, which determine
	 * whether the check passes:
	 *
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
	function check_permissions(&$entity, $type = 1) {
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
	 * Fill the $_SESSION['user'] variable with the logged in user's data.
	 *
	 * Also sets the default timezone to the user's timezone.
	 */
	function fill_session() {
		$tmp_user = user::factory($_SESSION['user_id']);
		if (is_object($_SESSION['user']) && $_SESSION['user']->equals($tmp_user)) {
			date_default_timezone_set($tmp_user->get_timezone());
			return;
		}
		unset($_SESSION['user']);
		$_SESSION['descendents'] = $this->get_group_descendents($tmp_user->group);
		foreach ($tmp_user->groups as $cur_group) {
			$_SESSION['descendents'] = array_merge($_SESSION['descendents'], $this->get_group_descendents($cur_group));
		}
		if ($tmp_user->inherit_abilities) {
			$_SESSION['inherited_abilities'] = $tmp_user->abilities;
			foreach ($tmp_user->groups as $cur_group) {
				$_SESSION['inherited_abilities'] = array_merge($_SESSION['inherited_abilities'], $cur_group->abilities);
			}
			if (isset($tmp_user->group))
				$_SESSION['inherited_abilities'] = array_merge($_SESSION['inherited_abilities'], $tmp_user->group->abilities);
		}
		$_SESSION['user'] = $tmp_user;
		date_default_timezone_set($tmp_user->get_timezone());
	}

	/**
	 * Check to see if a user has an ability.
	 *
	 * This function will check both user and group abilities, if the user is
	 * marked to inherit the abilities of his group.
	 * 
	 * If $ability and $user are null, it will check to see if a user is
	 * currently logged in.
	 *
	 * If the user has the "system/all" ability, this function will return true.
	 *
	 * @param string $ability The ability.
	 * @param user $user The user to check. If none is given, the current user is used.
	 * @return bool
	 */
	function gatekeeper($ability = NULL, $user = NULL) {
		if ( is_null($user) ) {
			// If the user is logged in, their abilities are already set up. We
			// just need to add them to the user's.
			if ( is_object($_SESSION['user']) ) {
				if ( is_null($ability) )
					return true;
				$user = $_SESSION['user'];
				// Check the cache to see if we've already checked this user.
				if (isset($this->gatekeeper_cache[$_SESSION['user_id']])) {
					$abilities = $this->gatekeeper_cache[$_SESSION['user_id']];
				} else {
					$abilities = $user->abilities;
					if (isset($_SESSION['inherited_abilities']))
						$abilities = array_merge($abilities, $_SESSION['inherited_abilities']);
					$this->gatekeeper_cache[$_SESSION['user_id']] = $abilities;
				}
			}
		} else {
			// If the user isn't logged in, their abilities need to be set up.
			// Check the cache to see if we've already checked this user.
			if (isset($this->gatekeeper_cache[$user->guid])) {
				$abilities = $this->gatekeeper_cache[$user->guid];
			} else {
				$abilities = $user->abilities;
				if ($user->inherit_abilities) {
					foreach ($user->groups as $cur_group) {
						$abilities = array_merge($abilities, $cur_group->abilities);
					}
					if (isset($user->group))
						$abilities = array_merge($abilities, $user->group->abilities);
				}
				$this->gatekeeper_cache[$user->guid] = $abilities;
			}
		}
		if ( !isset($user) )
			return false;
		if ( !is_array($abilities) )
			return false;
		return (in_array($ability, $abilities) || in_array('system/all', $abilities));
	}

	/**
	 * Gets an array of the components which can be a default component.
	 *
	 * The way a component can be a user's default components is to have a
	 * "default" action, which loads what the user will see when they first log
	 * on.
	 *
	 * @return array The array of component names.
	 */
	function get_default_component_array() {
		global $pines;
		$return = array();
		foreach ($pines->components as $cur_component) {
			if ( file_exists('components/'.$cur_component.'/actions/default.php') )
				$return[] = $cur_component;
		}
		return $return;
	}

	/**
	 * Gets an array of groups.
	 *
	 * If no parent is given, get_group_array() will start with all top level
	 * groups.
	 *
	 * get_group_array() returns a multidimensional hierarchical array. In each
	 * element is 'name', 'groupname', 'email', and 'children'. 'children' is an
	 * array of that group's children.
	 *
	 * @param group $parent The group to descend from.
	 * @return array The array of groups.
	 * @todo Check for orphans, they could cause groups to be hidden.
	 */
	function get_group_array($parent = NULL) {
		global $pines;
		$return = array();
		if ( is_null($parent) ) {
			$entities = $pines->entity_manager->get_entities(array('tags' => array('com_user', 'group'), 'class' => group));
			foreach ($entities as $entity) {
				if ( is_null($entity->parent) ) {
					$child_array = $this->get_group_array($entity);
					$return[$entity->guid]['name'] = $entity->name;
					$return[$entity->guid]['groupname'] = $entity->groupname;
					$return[$entity->guid]['email'] = $entity->email;
					$return[$entity->guid]['children'] = $child_array;
				}
			}
		} else {
			$entities = $pines->entity_manager->get_entities(array('ref' => array('parent' => $parent), 'tags' => array('com_user', 'group'), 'class' => group));
			foreach ($entities as $entity) {
				$child_array = $this->get_group_array($entity);
				$return[$entity->guid]['name'] = $entity->name;
				$return[$entity->guid]['groupname'] = $entity->groupname;
				$return[$entity->guid]['email'] = $entity->email;
				$return[$entity->guid]['children'] = $child_array;
			}
		}
		return $return;
	}

	/**
	 * Gets an array of a group's descendendents.
	 *
	 * If no parent is given, get_group_descendents() will start with all top
	 * level groups. (It will return all top level groups' descendents.)
	 *
	 * get_group_descendents() returns an array of a group's descendents.
	 *
	 * @param group $parent The group to descend from.
	 * @return array The array of groups.
	 */
	function get_group_descendents($parent = NULL) {
		global $pines;
		$return = array();
		if ( is_null($parent) ) {
			$entities = $pines->entity_manager->get_entities(array('tags' => array('com_user', 'group'), 'class' => group));
			foreach ($entities as $entity) {
				if ( is_null($entity->parent) ) {
					$child_array = $this->get_group_descendents($entity);
					$return = array_merge($return, $child_array);
				}
			}
		} else {
			$entities = $pines->entity_manager->get_entities(array('ref' => array('parent' => $parent), 'tags' => array('com_user', 'group'), 'class' => group));
			foreach ($entities as $entity) {
				$child_array = $this->get_group_descendents($entity);
				$return = array_merge($return, array($entity), $child_array);
			}
		}
		return $return;
	}

	/**
	 * Fills a menu with a group hierarchy.
	 *
	 * @param menu &$menu The menu to fill.
	 * @param group $parent The parent group.
	 * @param bool $top_level Whether to work on the menu's top level.
	 */
	function get_group_menu(&$menu = NULL, $parent = NULL, $top_level = TRUE) {
		global $pines;
		if ( is_null($parent) ) {
			$entities = $pines->entity_manager->get_entities(array('tags' => array('com_user', 'group'), 'class' => group));
			foreach ($entities as $entity) {
				$menu->add("{$entity->name} [{$entity->groupname}]", $entity->guid, $entity->parent->guid, $entity->guid);
			}
			$orphans = $menu->orphans();
			if ( !empty($orphans) ) {
				$orphan_menu_id = $menu->add('Orphans', NULL);
				foreach ($orphans as $orphan) {
					$menu->add($orphan['name'], $orphan['data'], $orphan_menu_id, $orphan['data']);
				}
			}
		} else {
			$entities = $pines->entity_manager->get_entities(array('ref' => array('parent' => $parent), 'tags' => array('com_user', 'group'), 'class' => group));
			foreach ($entities as $entity) {
				$new_menu_id = $menu->add("{$entity->name} [{$entity->groupname}]", $entity->guid, ($top_level ? NULL : $entity->parent->guid), $entity->guid);
				$this->get_group_menu($menu, $entity, FALSE);
			}
		}
	}

	/**
	 * Gets a tree style hierarchy of groups.
	 *
	 * The mask can contain these variables:
	 * #guid#
	 * #name#
	 * #groupname#
	 * #mark#
	 * #selected#
	 *
	 * For each depth level, $mark will be appended with "-> ".
	 *
	 * @param string $mask The line mask to fill with data.
	 * @param array $group_array An array of groups to work with.
	 * @see com_user::get_group_array()
	 * @param int|group|array $selected_id The ID/entity or array of IDs/entities on which to apply $selected to the mask.
	 * @param string $selected The selection text to apply to the mask on selected items.
	 * @param string $mark The mark to apply (per depth level) to the mask.
	 * @return string The rendered tree.
	 */
	function get_group_tree($mask, $group_array, $selected_id = NULL, $selected = ' selected="selected"', $mark = '') {
		$return = '';
		if (!is_array($group_array))
			return $return;
		foreach ($group_array as $key => $group) {
			$parsed = str_replace('#guid#', $key, $mask);
			$parsed = str_replace('#name#', $group['name'], $parsed);
			$parsed = str_replace('#groupname#', $group['groupname'], $parsed);
			$parsed = str_replace('#mark#', $mark, $parsed);
			if ( $key == $selected_id || $key == $selected_id->guid || (is_array($selected_id) && in_array($key, $selected_id)) || (is_array($selected_id) && group::factory($key)->in_array($selected_id)) ) {
				$parsed = str_replace('#selected#', $selected, $parsed);
			} else {
				$parsed = str_replace('#selected#', '', $parsed);
			}
			$return .= $parsed."\n";
			if ( !empty($group['children']) )
				$return .= $this->get_group_tree($mask, $group['children'], $selected_id, $selected, $mark.'-> ');
		}
		return $return;
	}

	/**
	 * Gets a group's groupname by its GUID.
	 *
	 * @param int $id The group's GUID.
	 * @return string|null The groupname if the group exists, null if it doesn't.
	 */
	function get_groupname($id) {
	// Check the cache to see if we've already queried this name.
		if (!isset($this->groupname_cache[$id])) {
			$entity = group::factory($id);
			if (is_null($entity->guid)) {
				$this->groupname_cache[$id] = null;
			} else {
				$this->groupname_cache[$id] = $entity->groupname;
			}
		}
		return $this->groupname_cache[$id];
	}

	/**
	 * Gets all groups.
	 *
	 * @return array An array of group entities.
	 */
	function get_groups() {
		global $pines;
		return $pines->entity_manager->get_entities(array('tags' => array('com_user', 'group'), 'class' => group));
	}

	/**
	 * Gets a user's username by its GUID.
	 *
	 * @param int $id The user's GUID.
	 * @return string|null The username if the user exists, null if it doesn't.
	 */
	function get_username($id) {
	// Check the cache to see if we've already queried this name.
		if (!isset($this->username_cache[$id])) {
			$entity = user::factory($id);
			if (is_null($entity->guid)) {
				$this->username_cache[$id] = null;
			} else {
				$this->username_cache[$id] = $entity->username;
			}
		}
		return $this->username_cache[$id];
	}

	/**
	 * Gets all users.
	 *
	 * @return array An array of user entities.
	 */
	function get_users() {
		global $pines;
		return $pines->entity_manager->get_entities(array('tags' => array('com_user', 'user'), 'class' => user));
	}

	/**
	 * Gets an array of users in a group.
	 *
	 * @param group $group The group.
	 * @return array An array of users.
	 */
	function get_users_by_group($group) {
		global $pines;
		return $pines->entity_manager->get_entities(array('ref_i' => array('group' => $group, 'groups' => $group), 'tags' => array('com_user', 'user'), 'class' => user));
	}

	/**
	 * Creates and attaches a module which lists groups.
	 */
	function list_groups() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_user', 'list_groups', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_user/list_groups'];

		$module->groups = $pines->entity_manager->get_entities(array('tags' => array('com_user', 'group'), 'class' => group));

		if ( empty($module->groups) ) {
			//$module->detach();
			display_notice('There are no groups.');
		}
	}

	/**
	 * Creates and attaches a module which lists users.
	 */
	function list_users() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_user', 'list_users', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_user/list_users'];

		$module->users = $pines->entity_manager->get_entities(array('tags' => array('com_user', 'user'), 'class' => user));

		if ( empty($module->users) ) {
			//$module->detach();
			display_notice('There are no users.');
		}
	}

	/**
	 * Logs the given user into the system.
	 *
	 * @param int $id The GUID of the user.
	 * @return bool True on success, false on failure.
	 */
	function login($id) {
		$entity = user::factory($id);

		if ( isset($entity->guid) ) {
			if ( $this->gatekeeper('com_user/login', $entity) ) {
				$_SESSION['user_id'] = $entity->guid;
				unset($_SESSION['user']);
				// We're changing users, so clear the gatekeeper cache.
				$this->gatekeeper_cache = array();
				$this->fill_session();
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Logs the current user out of the system.
	 */
	function logout() {
		unset($_SESSION['user']);
		session_unset();
		session_destroy();
	}

	/**
	 * Creates and attaches a module which let's the user log in.
	 *
	 * @param string $position The position in which to place the module.
	 */
	function print_login($position = 'content') {
		$module = new module('com_user', 'login', $position);
	}

	/**
	 * Kick the user out of the current page.
	 *
	 * Note that this method completely terminates execution of the script when
	 * it is called. Code after this function is called will not run.
	 *
	 * @param string $message An optional message to display to the user.
	 * @param string $url An optional URL to be included in the query data of the redirection url.
	 */
	function punt_user($message = NULL, $url = NULL) {
		global $pines;
		$default = '0';
		if ($pines->request_component == $_SESSION['user']->default_component && $pines->request_action == 'default')
			$default = '1';
		header("Location: ".pines_url('com_user', 'exit', array('default' => $default, 'message' => urlencode($message), 'url' => urlencode($url)), false));
		exit($message);
	}
}

?>