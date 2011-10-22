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
/* @var $pines pines */
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
	 * Activate the SAWASC system.
	 * @return bool True if SAWASC could be activated, false otherwise.
	 */
	public function activate_sawasc() {
		global $pines;
		if (!$pines->config->com_user->sawasc)
			return false;
		if ($pines->config->com_user->pw_method == 'salt') {
			pines_notice('SAWASC is not compatible with the Salt password storage method.');
			return false;
		}
		// Check that a challenge block was created within 10 minutes.
		if (!isset($_SESSION['sawasc']['ServerCB']) || $_SESSION['sawasc']['timestamp'] < time() - 600) {
			// If not, generate one.
			pines_session('write');
			$_SESSION['sawasc'] = array(
				'ServerCB' => uniqid('', true),
				'timestamp' => time(),
				'algo' => $pines->config->com_user->sawasc_hash
			);
			pines_session('close');
		}
		return true;
	}

	public function check_permissions(&$entity, $type = 1) {
		if ((object) $entity !== $entity)
			return false;
		if ((object) $_SESSION['user'] !== $_SESSION['user'])
			return true;
		if (function_exists('gatekeeper') && gatekeeper('system/all'))
			return true;
		if ($entity->has_tag('com_user', 'user') || $entity->has_tag('com_user', 'group'))
			return true;
		if (!isset($entity->user->guid) && !isset($entity->group->guid))
			return true;

		// Load access control, since we need it now...
		if ((object) $entity->ac === $entity->ac) {
			$ac = $entity->ac;
		} else {
			$ac = (object) array('user' => 3, 'group' => 3, 'other' => 0);
		}

		if (is_callable(array($entity->user, 'is')) && $entity->user->is($_SESSION['user']))
			return ($ac->user >= $type);
		if (is_callable(array($entity->group, 'is')) && ($entity->group->is($_SESSION['user']->group) || $entity->group->in_array($_SESSION['user']->groups) || $entity->group->in_array($_SESSION['descendents'])) )
			return ($ac->group >= $type);
		return ($ac->other >= $type);
	}

	public function fill_session() {
		global $pines;
		pines_session('write');
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
				pines_session('close');
				return;
			}
			unset($_SESSION['user']);
		} else {
			$tmp_user = user::factory($_SESSION['user_id']);
		}
		$_SESSION['user_timezone'] = $tmp_user->get_timezone();
		date_default_timezone_set($_SESSION['user_timezone']);
		if (isset($tmp_user->group))
			$_SESSION['descendents'] = (array) $tmp_user->group->get_descendents();
		foreach ($tmp_user->groups as $cur_group) {
			$_SESSION['descendents'] = array_merge((array) $_SESSION['descendents'], (array) $cur_group->get_descendents());
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
		pines_session('close');
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
				if ( !isset($ability) || empty($ability) )
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

	public function get_groups($all = false) {
		global $pines;

		$tags = array('com_user', 'group');
		if (!$all)
			$tags[] = 'enabled';

		return $pines->entity_manager->get_entities(
				array('class' => group),
				array('&',
					'tag' => $tags
				)
			);
	}

	public function get_users($all = false) {
		global $pines;

		$tags = array('com_user', 'user');
		if (!$all)
			$tags[] = 'enabled';

		return $pines->entity_manager->get_entities(
				array('class' => user),
				array('&',
					'tag' => $tags
				)
			);
	}

	public function group_sort(&$array, $property = null, $case_sensitive = false, $reverse = false) {
		global $pines;
		$pines->entity_manager->hsort($array, $property, 'parent', $case_sensitive, $reverse);
	}

	/**
	 * Creates and attaches a module which lists groups.
	 * 
	 * @param bool $enabled Show enabled groups if true, disabled if false.
	 * @return module The module.
	 */
	public function list_groups($enabled = true) {
		global $pines;

		$module = new module('com_user', 'list_groups', 'content');

		$module->enabled = $enabled;
		if ($enabled)
			$module->groups = $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group', 'enabled')));
		else
			$module->groups = $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group')), array('!&', 'tag' => 'enabled'));

		if ( empty($module->groups) )
			pines_notice('There are no'.($enabled ? ' enabled' : ' disabled').' groups.');

		return $module;
	}

	/**
	 * Creates and attaches a module which lists users.
	 * 
	 * @param bool $enabled Show enabled users if true, disabled if false.
	 * @return module The module.
	 */
	public function list_users($enabled = true) {
		global $pines;

		$module = new module('com_user', 'list_users', 'content');

		$module->enabled = $enabled;
		if ($enabled)
			$module->users = $pines->entity_manager->get_entities(array('class' => user), array('&', 'tag' => array('com_user', 'user', 'enabled')));
		else
			$module->users = $pines->entity_manager->get_entities(array('class' => user), array('&', 'tag' => array('com_user', 'user')), array('!&', 'tag' => 'enabled'));

		if ( empty($module->users) )
			pines_notice('There are no'.($enabled ? ' enabled' : ' disabled').' users.');

		return $module;
	}

	public function login($user) {
		if ( isset($user->guid) && $user->has_tag('com_user', 'user', 'enabled') && $this->gatekeeper('com_user/login', $user) ) {
			// Destroy session data.
			$this->logout();
			pines_session('write');
			$_SESSION['user_id'] = $user->guid;
			$this->fill_session();
			pines_session('close');
			return true;
		} else {
			return false;
		}
	}

	public function logout() {
		pines_session('write');
		unset($_SESSION['user_id']);
		unset($_SESSION['user']);
		// We're changing users, so clear the gatekeeper cache.
		$this->gatekeeper_cache = array();
		pines_session('destroy');
	}

	public function print_login($position = 'content', $url = null) {
		global $pines;
		$module = new module('com_user', 'modules/login', $position);
		$module->url = $url;
		if (isset($_REQUEST['url']))
			$module->url = $_REQUEST['url'];
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
		if (!isset($message))
			$message = isset($_SESSION['user']) ? 'You don\'t have necessary permission.' : 'Please log in first.';
		if (!empty($message))
			pines_notice($message);
		if ($query_part) {
			pines_redirect(pines_url('com_user', 'exit', $query_part));
		} else {
			pines_redirect(pines_url('com_user', 'exit'));
		}
		exit($message);
	}
}

?>