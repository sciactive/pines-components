<?php
/**
 * com_user's common file.
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
 * com_user main class.
 *
 * Provides an entity manager based user and group manager.
 *
 * @package Pines
 * @subpackage com_user
 */
class com_user extends component {
	function authenticate($username, $password) {
		$entity = new user;
		$entity = $this->get_user_by_username($username);
		if ( $entity->check_password($password) ) {
			return $entity->guid;
		} else {
			return null;
		}
	}

	function delete_group($group_id) {
		/**
         * @todo Delete children and remove users.
         */
		$entity = new group;
		if ( $entity = $this->get_group($group_id) ) {
			$entity->delete();
			return true;
		} else {
			return false;
		}
	}

	function delete_user($user_id) {
		$entity = new user;
		if ( $entity = $this->get_user($user_id) ) {
			$entity->delete();
			return true;
		} else {
			return false;
		}
	}

    function fill_session() {
        $_SESSION['user'] = $this->get_user($_SESSION['user_id']);
        if ($_SESSION['user']->inherit_abilities) {
            global $config;
            foreach ($_SESSION['user']->groups as $cur_group) {
                $cur_entity = $config->entity_manager->get_entity($cur_group, group);
                $_SESSION['user']->abilities = array_merge($_SESSION['user']->abilities, $cur_entity->abilities);
            }
        }
    }

    /**
     * Check to see if a user has an ability.
     *
     * This function will check both user and group abilities, if the user is
     * marked to inherit the abilities of his group.
     *
     * @param string $ability The ability.
     * @param user $user The user to check. If none is given, the current user is used.
     * @global DynamicConfig
     * @return bool
     */
	function gatekeeper($ability = NULL, $user = NULL) {
		if ( is_null($user) ) {
            // If the user is logged in, their abilities are already set up.
			if ( isset($_SESSION['user']) ) {
				$user = $_SESSION['user'];
			} else {
				unset($user);
			}
		} else {
            // If the user isn't logged in, their abilities need to be set up.
            if ($user->inherit_abilities) {
                global $config;
                foreach ($user->groups as $cur_group) {
                    $cur_entity = $config->entity_manager->get_entity($cur_group, group);
                    $user->abilities = array_merge($user->abilities, $cur_entity->abilities);
                }
            }
        }
		if ( isset($user) ) {
			if ( !is_null($ability) ) {
				if ( isset($user->abilities) ) {
					if ( in_array($ability, $user->abilities) || in_array('system/all', $user->abilities) )
						return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

    function get_default_component_array() {
        global $config;
        $return = array();
        foreach ($config->components as $cur_component) {
            if ( file_exists('components/'.$cur_component.'/actions/default.php') ) {
                array_push($return, $cur_component);
            }
        }
        return $return;
    }

	function get_group($group_id) {
        /**
         * @todo Rewrite specifically for groups.
         */
		global $config;
		$group = new group;
		$group = $config->entity_manager->get_entity($group_id, group);
		if ( empty($group) )
			return null;

		if ( $group->has_tag('com_user', 'group') ) {
			return $group;
		} else {
			return null;
		}
	}

	function get_group_array($parent_id = NULL) {
		// TODO: check for orphans, they could cause groups to be hidden
		global $config;
		$return = array();
		if ( is_null($parent_id) ) {
			$entities = $config->entity_manager->get_entities_by_tags('com_user', 'group', group);
			foreach ($entities as $entity) {
				if ( is_null($entity->parent) ) {
					$child_array = $this->get_group_array($entity->guid);
					$return[$entity->guid]['name'] = $entity->name;
					$return[$entity->guid]['groupname'] = $entity->groupname;
					$return[$entity->guid]['email'] = $entity->email;
					$return[$entity->guid]['children'] = $child_array;
				}
			}
		} else {
			$entities = $config->entity_manager->get_entities_by_parent($parent_id, group);
			foreach ($entities as $entity) {
				if ( $entity->has_tag('com_user', 'group') ) {
					$child_array = $this->get_group_array($entity->guid);
					$return[$entity->guid]['name'] = $entity->name;
					$return[$entity->guid]['groupname'] = $entity->groupname;
					$return[$entity->guid]['email'] = $entity->email;
					$return[$entity->guid]['children'] = $child_array;
				}
			}
		}
		return $return;
	}

	function get_group_by_groupname($groupname) {
		global $config;
		$entities = array();
		$entity = new group;
		$entities = $config->entity_manager->get_entities_by_data(array('groupname' => $groupname), array(), group);
		foreach ($entities as $entity) {
			if ( $entity->has_tag('com_user', 'group') )
				return $entity;
		}
		return null;
	}

	function get_groupname($group_id) {
		$entity = new group;
		$entity = $this->get_group($group_id);
		return $entity->groupname;
	}

	function get_user($user_id) {
		global $config;
		$user = new user;
		$user = $config->entity_manager->get_entity($user_id, user);
		if ( empty($user) )
			return null;

		if ( $user->has_tag('com_user', 'user') ) {
			return $user;
		} else {
			return null;
		}
	}

	function get_user_array($parent_id = NULL) {
		// TODO: check for orphans, they could cause users to be hidden
		global $config;
		$return = array();
		if ( is_null($parent_id) ) {
			$entities = $config->entity_manager->get_entities_by_tags('com_user', 'user', user);
			foreach ($entities as $entity) {
				if ( is_null($entity->parent) ) {
					$child_array = $this->get_user_array($entity->guid);
					$return[$entity->guid]['name'] = $entity->name;
					$return[$entity->guid]['username'] = $entity->username;
					$return[$entity->guid]['email'] = $entity->email;
					$return[$entity->guid]['children'] = $child_array;
				}
			}
		} else {
			$entities = $config->entity_manager->get_entities_by_parent($parent_id, user);
			foreach ($entities as $entity) {
				if ( $entity->has_tag('com_user', 'user') ) {
					$child_array = $this->get_user_array($entity->guid);
					$return[$entity->guid]['name'] = $entity->name;
					$return[$entity->guid]['username'] = $entity->username;
					$return[$entity->guid]['email'] = $entity->email;
					$return[$entity->guid]['children'] = $child_array;
				}
			}
		}
		return $return;
	}

	function get_user_by_username($username) {
		global $config;
		$entities = array();
		$entity = new user;
		$entities = $config->entity_manager->get_entities_by_data(array('username' => $username), array(), user);
		foreach ($entities as $entity) {
			if ( $entity->has_tag('com_user', 'user') )
				return $entity;
		}
		return null;
	}

	function get_user_menu($parent_id = NULL, &$menu = NULL, $menu_parent = NULL, $top_level = TRUE) {
		global $config;
		if ( is_null($parent_id) ) {
			$entities = $config->entity_manager->get_entities_by_tags('com_user', 'user', user);
			foreach ($entities as $entity) {
				$menu->add($entity->name.' ['.$entity->username.']', $entity->guid, $entity->parent, $entity->guid);
			}
			$orphans = $menu->orphans();
			if ( !empty($orphans) )
				$orphan_menu_id = $menu->add('Orphans', NULL);
			foreach ($orphans as $orphan) {
				$menu->add($orphan['name'], $orphan['data'], $orphan_menu_id, $orphan['data']);
			}
		} else {
			$entities = $config->entity_manager->get_entities_by_parent($parent_id);
			foreach ($entities as $entity) {
				$new_menu_id = $menu->add($entity->name.' ['.$entity->username.']', $entity->guid, ($top_level ? NULL : $entity->parent), $entity->guid);
				$this->get_user_menu($entity->guid, $menu, $new_menu_id, FALSE);
			}
		}
	}

	function get_username($user_id) {
		$entity = new user;
		$entity = $this->get_user($user_id);
		return $entity->username;
	}

    function list_groups() {
		global $config;

        $module = new module('com_user', 'list_groups', 'content');
		$module->title = "Groups";

		$module->groups = $config->entity_manager->get_entities_by_tags('com_user', 'group', group);

		if ( empty($module->groups) ) {
            $module->detach();
            display_notice("There are no groups.");
        }
    }

	function list_users() {
		global $config;

		$module = new module('com_user', 'list_users', 'content');
		$module->title = "Users";

		$module->users = $config->entity_manager->get_entities_by_tags('com_user', 'user', user);

		if ( empty($module->users) ) {
            $module->detach();
            display_notice("There are no users.");
        }

		/*
        $menu = new menu;
		$this->get_user_menu(NULL, $menu);
		$module->content($menu->render(array('<ul class="dropdown dropdown-vertical">', '</ul>'),
				array('<li>', '</li>'),
				array('<ul>', '</ul>'),
				array('<li>', '</li>'),
				"<strong>#NAME#</strong><br />".
					"<input type=\"button\" onclick=\"window.location='".$config->template->url('com_user', 'edituser', array('user_id' => '#DATA#'))."';\" value=\"Edit\" /> | ".
					"<input type=\"button\" onclick=\"if(confirm('Are you sure you want to delete \\'#NAME#\\'?')) {window.location='".$config->template->url('com_user', 'deleteuser', array('user_id' => '#DATA#'))."';}\" value=\"Delete\" />\n",
				'<hr style="visibility: hidden; clear: both;" />'));
         */
	}

	function login($id) {
		$entity = new user;

		$entity = $this->get_user($id);

		if ( isset($entity->username) ) {
			if ( $this->gatekeeper('com_user/login', $entity) ) {
				$_SESSION['user_id'] = $entity->guid;
				$this->fill_session();
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function logout() {
		unset($_SESSION['user']);
		session_unset();
		session_destroy();
	}

	function new_group() {
		$new_group = new user;
		$new_group->add_tag('com_user', 'group');
		$new_group->abilities = array();
		return $new_group;
	}

	function new_user() {
		$new_user = new user;
		$new_user->add_tag('com_user', 'user');
		$new_user->salt = md5(rand());
		$new_user->abilities = array();
		$new_user->groups = array();
        $new_user->inherit_abilities = true;
        $new_user->default_component = 'com_user';
		return $new_user;
	}

	function print_login($position = 'content') {
		$module = new module('com_user', 'login', $position);
	}

	function print_group_form($heading, $new_option, $new_action, $id = NULL) {
		global $config, $page;
		$module = new module('com_user', 'group_form', 'content');
		if ( is_null($id) ) {
			$module->groupname = $module->name = '';
			$module->group_abilities = array();
		} else {
			$group = $this->get_group($id);
			$module->groupname = $group->groupname;
			$module->name = $group->name;
			$module->email = $group->email;
			$module->parent = $group->parent;
			$module->group_abilities = $group->abilities;
		}
        $module->heading = $heading;
        $module->new_option = $new_option;
        $module->new_action = $new_action;
        $module->id = $id;
        $module->display_abilities = gatekeeper("com_user/abilities");
        $module->sections = array('system');
        $module->group_array = $this->get_group_array();
        foreach ($config->components as $cur_component) {
            $module->sections[] = $cur_component;
        }
	}

	function print_group_tree($mask, $group_array, $selected_id = NULL, $selected = ' selected="selected"', $mark = '') {
		$return = '';
		foreach ($group_array as $key => $group) {
			$parsed = str_replace('#guid#', $key, $mask);
			$parsed = str_replace('#name#', $group['name'], $parsed);
			$parsed = str_replace('#groupname#', $group['groupname'], $parsed);
			$parsed = str_replace('#mark#', $mark, $parsed);
            if ( $key == $selected_id || (is_array($selected_id) && in_array($key, $selected_id)) ) {
				$parsed = str_replace('#selected#', $selected, $parsed);
			} else {
				$parsed = str_replace('#selected#', '', $parsed);
			}
			$return .= $parsed."\n";
			if ( !empty($group['children']) )
				$return .= $this->print_group_tree($mask, $group['children'], $selected_id, $selected, $mark.'-> ');
		}
		return $return;
	}

	function print_user_form($heading, $new_option, $new_action, $id = NULL) {
		global $config, $page;
		$module = new module('com_user', 'user_form', 'content');
		if ( is_null($id) ) {
			$module->username = $module->name = '';
			$module->user_abilities = array();
            $module->groups = array();
            $module->inherit_abilities = true;
            $module->default_component = 'com_user';
		} else {
			$user = $this->get_user($id);
			$module->username = $user->username;
			$module->name = $user->name;
			$module->email = $user->email;
			$module->parent = $user->parent;
			$module->user_abilities = $user->abilities;
            $module->groups = $user->groups;
            $module->inherit_abilities = $user->inherit_abilities;
            $module->default_component = $user->default_component;
		}
        $module->heading = $heading;
        $module->new_option = $new_option;
        $module->new_action = $new_action;
        $module->id = $id;
        $module->display_groups = gatekeeper("com_user/assigng");
        $module->display_abilities = gatekeeper("com_user/abilities");
        $module->display_default_components = gatekeeper("com_user/default_component");
        $module->sections = array('system');
        $module->group_array = $this->get_group_array();
        $module->default_components = $this->get_default_component_array();
        foreach ($config->components as $cur_component) {
            $module->sections[] = $cur_component;
        }
		//$module->content("<label>Parent<select name=\"parent\">\n");
		//$module->content("<option value=\"none\">--No Parent--</option>\n");
		//$module->content($this->print_user_tree('<option value="#guid#"#selected#>#mark# #name# [#username#]</option>', $this->get_user_array(), $parent));
		//$module->content("</select></label>\n");
	}

	function print_user_tree($mask, $user_array, $selected_id = NULL, $selected = ' selected="selected"', $mark = '') {
		$return = '';
		foreach ($user_array as $key => $user) {
			$parsed = str_replace('#guid#', $key, $mask);
			$parsed = str_replace('#name#', $user['name'], $parsed);
			$parsed = str_replace('#username#', $user['username'], $parsed);
			$parsed = str_replace('#mark#', $mark, $parsed);
			if ( $key == $selected_id ) {
				$parsed = str_replace('#selected#', $selected, $parsed);
			} else {
				$parsed = str_replace('#selected#', '', $parsed);
			}
			$return .= $parsed."\n";
			if ( !empty($user['children']) )
				$return .= $this->print_user_tree($mask, $user['children'], $selected_id, $selected, $mark.'->');
		}
		return $return;
	}

	function punt_user($message = NULL, $url = NULL) {
		global $config;
		header("Location: ".$config->template->url('com_user', 'exit', array('message' => urlencode($message), 'url' => urlencode($url)), false));
		exit;
	}
}

/**
 * The user manager.
 * @global com_user $config->user_manager
 */
$config->user_manager = new com_user;
$config->ability_manager->add('com_user', 'login', 'Login', 'User can login to the system.');
$config->ability_manager->add('com_user', 'self', 'Change Info', 'User can change his own information.');
$config->ability_manager->add('com_user', 'default_component', 'Change Default Component', 'User can change default component.');
$config->ability_manager->add('com_user', 'new', 'Create Users', 'Let user create new users.');
$config->ability_manager->add('com_user', 'manage', 'Manage Users', 'Let user see and manage other users. Required to access the below abilities.');
$config->ability_manager->add('com_user', 'edit', 'Edit Users', 'Let user edit other users\' details.');
$config->ability_manager->add('com_user', 'delete', 'Delete Users', 'Let user delete other users.');
$config->ability_manager->add('com_user', 'assigng', 'Assign Groups', 'Let user assign users to groups, possibly granting them more abilities.');
$config->ability_manager->add('com_user', 'newg', 'Create Groups', 'Let user create new groups.');
$config->ability_manager->add('com_user', 'manageg', 'Manage Groups', 'Let user see and manage groups. Required to access the below abilities.');
$config->ability_manager->add('com_user', 'editg', 'Edit Groups', 'Let user edit groups\' details.');
$config->ability_manager->add('com_user', 'deleteg', 'Delete Groups', 'Let user delete groups.');
$config->ability_manager->add('com_user', 'abilities', 'Manage Abilities', 'Let user manage other users\' and his own abilities.');

if ( isset($_SESSION['user_id']) ) {
    $config->user_manager->fill_session();
}

?>