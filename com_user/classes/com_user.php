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
	 * @var array
	 */
	private $gatekeeper_cache = array();
	/**
	 * Group property to sort by.
	 *
	 * @access private
	 * @var array
	 */
	private $sort_property;

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
				$_SESSION['user']->clear_cache();
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

	public function group_sort(&$array, $property = null, $reverse = false) {
		$new_array = array();
		// Count the children.
		$child_counter = array();
		// First sort by the requested property.
		if (isset($property)) {
			$this->sort_property = $property;
			@usort($array, array($this, 'group_sort_property'));
		}
		if ($reverse)
			$array = array_reverse($array);
		// Now sort by children.
		while ($array) {
			// Look for groups ready to go in order.
			$changed = false;
			foreach ($array as $key => &$cur_group) {
				// Must break after adding one, so any following children don't go in the wrong order.
				if (!isset($cur_group->parent) || !$cur_group->parent->in_array(array_merge($new_array, $array))) {
					// If they have no parent (or their parent isn't in the array), they go on the end.
					$new_array[] = $cur_group;
					unset($array[$key]);
					$changed = true;
					break;
				} else {
					// Else find the parent.
					$pkey = $cur_group->parent->array_search($new_array);
					if ($pkey !== false) {
						// And insert after the parent.
						// This makes groups go to the end of the child list.
						$cur_ancestor = $cur_group->parent;
						while (isset($cur_ancestor)) {
							$child_counter[$cur_ancestor->guid]++;
							$cur_ancestor = $cur_ancestor->parent;
						}
						// Where to place the group.
						$new_key = $pkey + $child_counter[$cur_group->parent->guid];
						if (isset($new_array[$new_key])) {
							// If it already exists, we have to splice it in.
							array_splice($new_array, $new_key, 0, array($cur_group));
							$new_array = array_values($new_array);
						} else {
							// Else just add it.
							$new_array[$new_key] = $cur_group;
						}
						unset($array[$key]);
						$changed = true;
						break;
					}
				}
			}
			unset($cur_group);
			if (!$changed) {
				// If there are any unexpected errors and the array isn't changed, just stick the rest on the end.
				$groups_left = array_splice($array, 0);
				$new_array = array_merge($new_array, $groups_left);
			}
		}
		// Now push the new array out.
		$array = $new_array;
	}

	/**
	 * Determine the sort order between two groups.
	 * 
	 * @param group $a Group A.
	 * @param group $b Group B.
	 * @return int Sort order.
	 * @access private
	 */
	private function group_sort_property($a, $b) {
		$property = $this->sort_property;
		if ($a->$property > $b->$property)
			return 1;
		if ($a->$property < $b->$property)
			return -1;
		return 0;
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
		if (
				(empty($pines->request_component) && empty($pines->request_action)) ||
				($pines->request_component == $pines->config->default_component && $pines->request_action == 'default')
			)
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