<?php
/**
 * com_user class.
 *
 * @package Components\user
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
 * @package Components\user
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
	 * Whether the user selector JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded_cust
	 */
	private $js_loaded_user = false;
	
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
		if ((object) $entity->ac === $entity->ac)
			$ac = $entity->ac;
		else
			$ac = (object) array('user' => 3, 'group' => 3, 'other' => 0);

		if (is_callable(array($entity->user, 'is')) && $entity->user->is($_SESSION['user']))
			return ($ac->user >= $type);
		if (is_callable(array($entity->group, 'is')) && ($entity->group->is($_SESSION['user']->group) || $entity->group->in_array($_SESSION['user']->groups) || $entity->group->in_array($_SESSION['descendants'])) )
			return ($ac->group >= $type);
		return ($ac->other >= $type);
	}

	/**
	 * Check that a username is valid.
	 * 
	 * The ID of a user can be given so that user is excluded when checking if
	 * the name is already in use.
	 * 
	 * @param string $username The username to check.
	 * @param int $id The GUID of the user for which the name is being checked.
	 * @return array An associative array with a boolean 'result' entry and a 'message' entry.
	 */
	public function check_username($username, $id = null) {
		global $pines;
		if (!$pines->config->com_user->email_usernames) {
			if (empty($username))
				return array('result' => false, 'message' => 'Please specify a username.');
			if ($pines->config->com_user->max_username_length > 0 && strlen($username) > $pines->config->com_user->max_username_length)
				return array('result' => false, 'message' => "Usernames must not exceed {$pines->config->com_user->max_username_length} characters.");
			if (array_diff(str_split($username), str_split($pines->config->com_user->valid_chars)))
				return array('result' => false, 'message' => $pines->config->com_user->valid_chars_notice);
			if (!preg_match($pines->config->com_user->valid_regex, $username))
				return array('result' => false, 'message' => $pines->config->com_user->valid_regex_notice);
			$selector = array('&',
					'strict' => array('username', $username)
				);
			if (isset($id) && $id > 0)
				$selector['!guid'] = $id;
			$test = $pines->entity_manager->get_entity(
					array('class' => user, 'skip_ac' => true),
					$selector
				);
			if (isset($test->guid))
				return array('result' => false, 'message' => 'That username is taken.');
				
			return array('result' => true, 'message' => (isset($id) ? 'Username is valid.' : 'Username is available!'));
		} else {
			if (empty($username))
				return array('result' => false, 'message' => 'Please specify an email.');
			if ($pines->config->com_user->max_username_length > 0 && strlen($username) > $pines->config->com_user->max_username_length)
				return array('result' => false, 'message' => "Emails must not exceed {$pines->config->com_user->max_username_length} characters.");
			if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $username))
				return array('result' => false, 'message' => 'Email must be a correctly formatted address.');
			$selector = array('&',
					'strict' => array('email', $username)
				);
			if (isset($id) && $id > 0)
				$selector['!guid'] = $id;
			$test = $pines->entity_manager->get_entity(
					array('class' => user, 'skip_ac' => true),
					$selector
				);
			if (isset($test->guid))
				return array('result' => false, 'message' => 'That email address is already registered.');

			return array('result' => true, 'message' => (isset($id) ? 'Email is valid.' : 'Email address is valid!'));
		}
	}
	
	/**
	 * Check that an email is unique.
	 * 
	 * The ID of a user can be given so that user is excluded when checking if
	 * the email is already in use.
	 * 
	 * Wrote this mainly for quick ajax testing of the email for user sign up on
	 * an application.
	 * 
	 * @param string $email The email to check.
	 * @param int $id The GUID of the user for which the email is being checked.
	 * @return array An associative array with a boolean 'result' entry and a 'message' entry.
	 */
	public function check_email($email, $id = null) {
		global $pines;
		
		if (empty($email))
			return array('result' => false, 'message' => 'Please specify an email.');
		if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email))
			return array('result' => false, 'message' => 'Email must be a correctly formatted address.');
		$selector = array('&',
				'strict' => array('email', $email)
			);
		if (isset($id) && $id > 0)
			$selector['!guid'] = $id;
		$test = $pines->entity_manager->get_entity(
				array('class' => user, 'skip_ac' => true),
				$selector
			);
		if (isset($test->guid))
			return array('result' => false, 'message' => 'That email address is already registered.');

		return array('result' => true, 'message' => (isset($id) ? 'Email is valid.' : 'Email address is valid!'));
	}
	
	/**
	 * Check that a phone number is unique.
	 * 
	 * The ID of a user can be given so that user is excluded when checking if
	 * the phone is already in use.
	 * 
	 * Wrote this mainly for quick ajax testing of the phone for user sign up on
	 * an application.
	 * 
	 * @param string $phone The phone to check.
	 * @param int $id The GUID of the user for which the phone is being checked.
	 * @return array An associative array with a boolean 'result' entry and a 'message' entry.
	 */
	public function check_phone($phone, $id = null) {
		global $pines;
		
		if (empty($phone))
			return array('result' => false, 'message' => 'Please specify a phone number.');
		
		$strip_to_digits = preg_replace('/\D/', '', $phone);
		if (!preg_match('/\d{10}/', $strip_to_digits))
			return array('result' => false, 'message' => 'Phone must contain 10 digits, but formatting does not matter.');
		$selector = array('&',
				'tag' => array('com_user', 'user')
			);
		$or = array('|',
				'data' => array(
					array('phone_cell', $strip_to_digits),
					array('phone', $strip_to_digits)
				)
			);
		if (isset($id) && $id > 0)
			$selector['!guid'] = $id;
		$test = $pines->entity_manager->get_entity(
				array('class' => user, 'skip_ac' => true),
				$selector, $or
			);
		if (isset($test->guid))
			return array('result' => false, 'message' => 'Phone number is in use.');

		return array('result' => true, 'message' => (isset($id) ? 'Phone number is valid.' : 'Phone number is valid!'));
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
		} else
			$tmp_user = user::factory($_SESSION['user_id']);
		$_SESSION['user_timezone'] = $tmp_user->get_timezone();
		date_default_timezone_set($_SESSION['user_timezone']);
		if (isset($tmp_user->group))
			$_SESSION['descendants'] = (array) $tmp_user->group->get_descendants();
		foreach ($tmp_user->groups as $cur_group)
			$_SESSION['descendants'] = array_merge((array) $_SESSION['descendants'], (array) $cur_group->get_descendants());
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
		} else {
			$_SESSION['abilities'] = $tmp_user->abilities;
		}
		$_SESSION['user'] = $tmp_user;
		// Because we are way passed inits, 
		// the custom config in com_configure i14 will never happen
		// and because of caching, it will not get properly loaded like it should
		// here.
		if (($tmp_user->sys_config || $tmp_user->com_config) && $pines->depend->check('component', 'com_example'))
			$pines->configurator->load_per_user_array($tmp_user->sys_config, $tmp_user->com_config);
		pines_session('close');
	}

	/**
	 * Check to see if a user has an ability.
	 *
	 * This function will check both user and group abilities, if the user is
	 * marked to inherit the abilities of its group.
	 */
	public function gatekeeper($ability = null, $user = null) {
		if (!isset($user)) {
			// If the user is logged in, their abilities are already set up. We
			// just need to add them to the user's.
			if ((object) $_SESSION['user'] === $_SESSION['user']) {
				if ( !isset($ability) || empty($ability) )
					return true;
				$user =& $_SESSION['user'];
				// Check the cache to see if we've already checked this user.
				if (isset($this->gatekeeper_cache[$_SESSION['user_id']]))
					$abilities =& $this->gatekeeper_cache[$_SESSION['user_id']];
				else {
					$abilities = $user->abilities;
					if (isset($_SESSION['inherited_abilities']))
						$abilities = array_merge($abilities, $_SESSION['inherited_abilities']);
					$this->gatekeeper_cache[$_SESSION['user_id']] = $abilities;
				}
			}
		} else {
			// If the user isn't logged in, their abilities need to be set up.
			// Check the cache to see if we've already checked this user.
			if (isset($this->gatekeeper_cache[$user->guid]))
				$abilities =& $this->gatekeeper_cache[$user->guid];
			else {
				$abilities = $user->abilities;
				// TODO: Decide if group conditions should be checked if the user is not logged in.
				if ($user->inherit_abilities) {
					foreach ($user->groups as &$cur_group)
						$abilities = array_merge($abilities, $cur_group->abilities);
					unset($cur_group);
					if (isset($user->group))
						$abilities = array_merge($abilities, $user->group->abilities);
				}
				$this->gatekeeper_cache[$user->guid] = $abilities;
			}
		}
		if (!isset($user) || ((array) $abilities !== $abilities))
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

		if (empty($module->groups))
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

		if (empty($module->users))
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
		} else
			return false;
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
		if ($query_part)
			pines_redirect(pines_url('com_user', 'exit', $query_part));
		else
			pines_redirect(pines_url('com_user', 'exit'));
		exit($message);
	}
	
	/**
	 * Load the user selector.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load_user_select() {
		if (!$this->js_loaded_user) {
			$module = new module('com_user', 'select', 'head');
			$module->render();
			$this->js_loaded_user = true;
		}
	}
}

?>