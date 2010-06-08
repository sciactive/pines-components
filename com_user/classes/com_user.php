<?php
/**
 * com_user class.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_user main class.
 *
 * Provides an entity based user and group manager.
 *
 * @package Pines
 * @subpackage com_user
 */
class com_user extends component implements user_manager_interface {
	/**
	 * Gatekeeper ability cache.
	 *
	 * Gatekeeper will cache users' abilities that it calculates, so it can
	 * check faster if that user has been checked before.
	 *
	 * @access private
	 * @var array $gatekeeper_cache
	 */
	private $gatekeeper_cache = array();

	public function check_permissions(&$entity, $type = 1) {
		if ((object) $_SESSION['user'] !== $_SESSION['user'])
			return true;
		if (function_exists('gatekeeper') && gatekeeper('system/all'))
			return true;
		if (!isset($entity->user->guid) && !isset($entity->group->guid))
			return true;
		if ($entity->is($_SESSION['user']))
			return true;
		if ($entity->is($_SESSION['user']->group))
			return true;
		if ($entity->has_tag('com_user', 'user') || $entity->has_tag('com_user', 'group'))
			return true;

		// Load access control, since we need it now...
		if ((object) $entity->ac === $entity->ac) {
			$ac = $entity->ac;
		} else {
			$ac = (object) array('user' => 3, 'group' => 3, 'other' => 0);
		}
		
		if (is_callable(array($entity->user, 'is')) && $entity->user->is($_SESSION['user']))
			return ($ac->user >= $type);
		if (is_callable(array($entity->group, 'is')) && $entity->group->is($_SESSION['user']->group) ||
			$entity->group->in_array($_SESSION['user']->groups) ||
			$entity->group->in_array($_SESSION['descendents']) )
			return ($ac->group >= $type);
		return ($ac->other >= $type);
	}

	public function fill_session() {
		global $pines;
		if ((object) $_SESSION['user'] === $_SESSION['user']) {
			$tmp_user = $pines->entity_manager->get_entity(
					array('class' => user),
					array('&',
						'guid' => array($_SESSION['user']->guid),
						'gt' => array('p_mdate', $_SESSION['user']->p_mdate)
					)
				);
			if (!isset($tmp_user)) {
				date_default_timezone_set($_SESSION['user_timezone']);
				return;
			}
			unset($_SESSION['user']);
		} else {
			$tmp_user = user::factory($_SESSION['user_id']);
		}
		$_SESSION['user_timezone'] = $tmp_user->get_timezone();
		date_default_timezone_set($_SESSION['user_timezone']);
		if (isset($tmp_user->group))
			$_SESSION['descendents'] = $tmp_user->group->get_descendents();
		foreach ($tmp_user->groups as $cur_group) {
			$_SESSION['descendents'] = array_merge($_SESSION['descendents'], $cur_group->get_descendents());
		}
		if ($tmp_user->inherit_abilities) {
			$_SESSION['inherited_abilities'] = $tmp_user->abilities;
			foreach ($tmp_user->groups as $cur_group) {
				// Check that any group conditions are met before adding the abilities.
				if ($cur_group->conditions && $pines->config->com_user->conditional_groups) {
					$pass = true;
					foreach ($cur_group->conditions as $cur_type => $cur_value) {
						if (!$pines->depend->check($cur_type, $cur_value)) {
							$pass = false;
							break;
						}
					}
					if (!$pass)
						continue;
				}
				// Any conditions are met, so add this group's abilities.
				$_SESSION['inherited_abilities'] = array_merge($_SESSION['inherited_abilities'], $cur_group->abilities);
			}
			if (isset($tmp_user->group)) {
				// Check that any group conditions are met before adding the abilities.
				$pass = true;
				if ($tmp_user->group->conditions && $pines->config->com_user->conditional_groups) {
					foreach ($tmp_user->group->conditions as $cur_type => $cur_value) {
						if (!$pines->depend->check($cur_type, $cur_value)) {
							$pass = false;
							break;
						}
					}
				}
				// If all conditions are met, add this group's abilities.
				if ($pass)
					$_SESSION['inherited_abilities'] = array_merge($_SESSION['inherited_abilities'], $tmp_user->group->abilities);
			}
		}
		$_SESSION['user'] = $tmp_user;
	}

	/**
	 * Check to see if a user has an ability.
	 *
	 * This function will check both user and group abilities, if the user is
	 * marked to inherit the abilities of its group.
	 */
	public function gatekeeper($ability = null, $user = null) {
		if ( !isset($user) ) {
			// If the user is logged in, their abilities are already set up. We
			// just need to add them to the user's.
			if ( (object) $_SESSION['user'] === $_SESSION['user'] ) {
				if ( !isset($ability) )
					return true;
				$user =& $_SESSION['user'];
				// Check the cache to see if we've already checked this user.
				if (isset($this->gatekeeper_cache[$_SESSION['user_id']])) {
					$abilities =& $this->gatekeeper_cache[$_SESSION['user_id']];
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
				$abilities =& $this->gatekeeper_cache[$user->guid];
			} else {
				$abilities = $user->abilities;
				// TODO: Decide if group conditions should be checked if the user is not logged in.
				if ($user->inherit_abilities) {
					foreach ($user->groups as &$cur_group) {
						$abilities = array_merge($abilities, $cur_group->abilities);
					}
					unset($cur_group);
					if (isset($user->group))
						$abilities = array_merge($abilities, $user->group->abilities);
				}
				$this->gatekeeper_cache[$user->guid] = $abilities;
			}
		}
		if ( !isset($user) || ((array) $abilities !== $abilities) )
			return false;
		return (in_array($ability, $abilities) || in_array('system/all', $abilities));
	}

	/**
	 * Gets a multidimensional array of group info.
	 *
	 * If no parent is given, get_group_array() will start with all top level
	 * groups.
	 *
	 * get_group_array() returns a multidimensional hierarchical array. In each
	 * element is 'name', 'groupname', 'email', and 'children'. 'children' is an
	 * array of that group's children.
	 *
	 * @param group $parent The group to descend from.
	 * @return array The group structure array.
	 * @todo Check for orphans, they could cause groups to be hidden.
	 */
	public function get_group_array($parent = null) {
		global $pines;
		$return = array();
		if ( !isset($parent) ) {
			$entities = $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group')));
			foreach ($entities as $entity) {
				if ( !isset($entity->parent) ) {
					$child_array = $this->get_group_array($entity);
					$return[$entity->guid]['name'] = $entity->name;
					$return[$entity->guid]['groupname'] = $entity->groupname;
					$return[$entity->guid]['email'] = $entity->email;
					$return[$entity->guid]['children'] = $child_array;
				}
			}
		} else {
			$entities = $pines->entity_manager->get_entities(array('class' => group), array('&', 'ref' => array('parent', $parent), 'tag' => array('com_user', 'group')));
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
	 * Fills a menu with a group hierarchy.
	 *
	 * @param menu &$menu The menu to fill.
	 * @param group $parent The parent group.
	 * @param bool $top_level Whether to work on the menu's top level.
	 */
	public function get_group_menu(&$menu = null, $parent = null, $top_level = true) {
		global $pines;
		if ( !isset($parent) ) {
			$entities = $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group')));
			foreach ($entities as $entity) {
				$menu->add("{$entity->name} [{$entity->groupname}]", $entity->guid, $entity->parent->guid, $entity->guid);
			}
			$orphans = $menu->orphans();
			if ( !empty($orphans) ) {
				$orphan_menu_id = $menu->add('Orphans', null);
				foreach ($orphans as $orphan) {
					$menu->add($orphan['name'], $orphan['data'], $orphan_menu_id, $orphan['data']);
				}
			}
		} else {
			$entities = $pines->entity_manager->get_entities(array('class' => group), array('&', 'ref' => array('parent', $parent), 'tag' => array('com_user', 'group')));
			foreach ($entities as $entity) {
				$new_menu_id = $menu->add("{$entity->name} [{$entity->groupname}]", $entity->guid, ($top_level ? null : $entity->parent->guid), $entity->guid);
				$this->get_group_menu($menu, $entity, false);
			}
		}
	}

	/**
	 * Gets a tree style hierarchy of groups.
	 *
	 * The mask can contain these variables:
	 *
	 * - #guid#
	 * - #name#
	 * - #groupname#
	 * - #mark#
	 * - #selected#
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
	public function get_group_tree($mask, $group_array, $selected_id = null, $selected = ' selected="selected"', $mark = '') {
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

	public function get_groups() {
		global $pines;
		return $pines->entity_manager->get_entities(
				array('class' => group),
				array('&',
					'data' => array('enabled', true),
					'tag' => array('com_user', 'group')
				)
			);
	}

	public function get_users() {
		global $pines;
		return $pines->entity_manager->get_entities(
				array('class' => user),
				array('&',
					'data' => array('enabled', true),
					'tag' => array('com_user', 'user')
				)
			);
	}

	/**
	 * Creates and attaches a module which lists groups.
	 * 
	 * @param bool $enabled Show enabled groups if true, disabled if false.
	 */
	public function list_groups($enabled = true) {
		global $pines;

		$module = new module('com_user', 'list_groups', 'content');

		$module->groups = $pines->entity_manager->get_entities(array('class' => group), array('&', 'data' => array('enabled', !!$enabled), 'tag' => array('com_user', 'group')));

		if ( empty($module->groups) )
			pines_notice('There are no'.($enabled ? ' enabled' : ' disabled').' groups.');
	}

	/**
	 * Creates and attaches a module which lists users.
	 * 
	 * @param bool $enabled Show enabled users if true, disabled if false.
	 */
	public function list_users($enabled = true) {
		global $pines;

		$module = new module('com_user', 'list_users', 'content');

		$module->users = $pines->entity_manager->get_entities(array('class' => user), array('&', 'data' => array('enabled', !!$enabled), 'tag' => array('com_user', 'user')));

		if ( empty($module->users) )
			pines_notice('There are no'.($enabled ? ' enabled' : ' disabled').' users.');
	}

	public function login($user) {
		if ( isset($user->guid) && $user->enabled && $this->gatekeeper('com_user/login', $user) ) {
			// Destroy session data.
			$this->logout();
			// Start a new session.
			session_start();
			$_SESSION['user_id'] = $user->guid;
			$this->fill_session();
			return true;
		} else {
			return false;
		}
	}

	public function logout() {
		unset($_SESSION['user_id']);
		unset($_SESSION['user']);
		// We're changing users, so clear the gatekeeper cache.
		$this->gatekeeper_cache = array();
		@session_unset();
		@session_destroy();
	}

	public function print_login($position = 'content') {
		$module = new module('com_user', 'login', $position);
		return $module;
	}

	public function punt_user($message = null, $url = null) {
		global $pines;
		$query_part = array();
		if (isset($url))
			$query_part['url'] = $url;
		if ($pines->request_component == $_SESSION['user']->default_component && $pines->request_action == 'default')
			$query_part['default'] = '1';
		pines_notice($message);
		if ($query_part) {
			redirect(pines_url('com_user', 'exit', $query_part));
		} else {
			redirect(pines_url('com_user', 'exit'));
		}
		exit($message);
	}
}

?>