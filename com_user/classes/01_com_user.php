<?php
/**
 * com_user class.
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
		$entity = $this->get_user_by_username($username);
        if (is_null($entity)) return false;
		if ( $entity->check_password($password) ) {
			return $entity->guid;
		} else {
			return false;
		}
	}

    /**
     * Delete a group from the system.
     *
     * @param int $group_id The GUID of the group.
     * @return bool True on success, false on failure.
     * @todo Delete children and remove users.
     */
	function delete_group($group_id) {
		if ( $entity = $this->get_group($group_id) ) {
			$entity->delete();
            pines_log("Deleted group $entity->groupname.", 'notice');
			return true;
		} else {
			return false;
		}
	}

    /**
     * Delete a user from the system.
     *
     * @param int $user_id The GUID of the user.
     * @return bool True on success, false on failure.
     */
	function delete_user($user_id) {
		if ( $entity = $this->get_user($user_id) ) {
			$entity->delete();
            pines_log("Deleted user $entity->username.", 'notice');
			return true;
		} else {
			return false;
		}
	}

    /**
     * Fill the $_SESSION['user'] variable with the logged in user's data.
     */
    function fill_session() {
        $_SESSION['user'] = $this->get_user($_SESSION['user_id']);
        if ($_SESSION['user']->inherit_abilities) {
            global $config;
            $_SESSION['inherited_abilities'] = $_SESSION['user']->abilities;
            foreach ($_SESSION['user']->groups as $cur_group) {
                $cur_entity = $config->entity_manager->get_entity($cur_group, group);
                $_SESSION['inherited_abilities'] = array_merge($_SESSION['inherited_abilities'], $cur_entity->abilities);
            }
            if (isset($_SESSION['user']->group)) {
                $cur_entity = $config->entity_manager->get_entity($_SESSION['user']->group, group);
                $_SESSION['inherited_abilities'] = array_merge($_SESSION['inherited_abilities'], $cur_entity->abilities);
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
            // If the user is logged in, their abilities are already set up. We
            // just need to add them to the user.
			if ( isset($_SESSION['user']) ) {
				$user = clone $_SESSION['user'];
                if (isset($_SESSION['inherited_abilities'])) {
                    $user->abilities = array_merge($user->abilities, $_SESSION['inherited_abilities']);
                }
			} else {
				unset($user);
			}
		} else {
            // If the user isn't logged in, their abilities need to be set up.
            $user = clone $user;
            if ($user->inherit_abilities) {
                global $config;
                foreach ($user->groups as $cur_group) {
                    $cur_entity = $config->entity_manager->get_entity($cur_group, group);
                    $user->abilities = array_merge($user->abilities, $cur_entity->abilities);
                }
                if (isset($user->group)) {
                    $cur_entity = $config->entity_manager->get_entity($user->group, group);
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
        global $config;
        $return = array();
        foreach ($config->components as $cur_component) {
            if ( file_exists('components/'.$cur_component.'/actions/default.php') ) {
                array_push($return, $cur_component);
            }
        }
        return $return;
    }

    /**
     * Get's a group by GUID.
     *
     * @param int $group_id The group's GUID.
     * @return group|null The group if it exists, null if it doesn't.
     */
	function get_group($group_id) {
		global $config;
		$group = $config->entity_manager->get_entity($group_id, group);
		if ( empty($group) )
			return null;

		if ( $group->has_tag('com_user', 'group') ) {
			return $group;
		} else {
			return null;
		}
	}

    /**
     * Get's an array of groups.
     *
     * If no parent id is given, get_group_array() will start with all top level
     * groups.
     *
     * get_group_array() returns a multidimensional hierarchical array. In each
     * element is 'name', 'groupname', 'email', and 'children'. 'children' is an
     * array of that group's children.
     *
     * @param int $parent_id The GUID of the group to descend from.
     * @return array The array of groups.
     * @todo Check for orphans, they could cause groups to be hidden.
     */
	function get_group_array($parent_id = NULL) {
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

    /**
     * Get's a group by groupname.
     *
     * @param string $groupname The group's groupname.
     * @return group|null The group if it exists, null if it doesn't.
     */
	function get_group_by_groupname($groupname) {
		global $config;
		$entities = array();
		$entities = $config->entity_manager->get_entities_by_data(array('groupname' => $groupname), array(), group);
		foreach ($entities as $entity) {
			if ( $entity->has_tag('com_user', 'group') )
				return $entity;
		}
		return null;
	}
    
    /**
     * Fills a menu with a group hierarchy.
     * 
     * @param int $parent_id The GUID of the parent group.
     * @param menu &$menu The menu to fill.
     * @param bool $top_level Whether to work on the menu's top level.
     */
	function get_group_menu(&$menu = NULL, $parent_id = NULL, $top_level = TRUE) {
		global $config;
		if ( is_null($parent_id) ) {
			$entities = $config->entity_manager->get_entities_by_tags('com_user', 'group', group);
			foreach ($entities as $entity) {
				$menu->add($entity->name.' ['.$entity->groupname.']', $entity->guid, $entity->parent, $entity->guid);
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
				$new_menu_id = $menu->add($entity->name.' ['.$entity->groupname.']', $entity->guid, ($top_level ? NULL : $entity->parent), $entity->guid);
				$this->get_group_menu($entity->guid, $menu, $new_menu_id, FALSE);
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
     * @param int|array $selected_id The ID or array of IDs on which to apply $selected to the mask.
     * @param string $selected The selection text to apply to the mask on selected items.
     * @param string $mark The mark to apply (per depth level) to the mask.
     * @return string The rendered tree.
     */
	function get_group_tree($mask, $group_array, $selected_id = NULL, $selected = ' selected="selected"', $mark = '') {
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
				$return .= $this->get_group_tree($mask, $group['children'], $selected_id, $selected, $mark.'-> ');
		}
		return $return;
	}

    /**
     * Get's a group's groupname by its GUID.
     *
     * @param int $group_id The group's GUID.
     * @return string|null The groupname if the group exists, null if it doesn't.
     */
	function get_groupname($group_id) {
		$entity = $this->get_group($group_id);
        if (is_null($entity)) return null;
		return $entity->groupname;
	}

    /**
     * Get's a user by GUID.
     *
     * @param int $user_id The user's GUID.
     * @return user|null The user if it exists, null if it doesn't.
     */
	function get_user($user_id) {
		global $config;
		$user = $config->entity_manager->get_entity($user_id, user);
		if ( empty($user) )
			return null;

		if ( $user->has_tag('com_user', 'user') ) {
			return $user;
		} else {
			return null;
		}
	}

    /*
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
     */

    /**
     * Get's a user by username.
     *
     * If there are more than one user with the same username (which shouldn't
     * happen, but can), the first user found is returned.
     *
     * @param string $username The user's username.
     * @return user|null The user if it exists, null if it doesn't.
     */
	function get_user_by_username($username) {
		global $config;
		$entities = array();
		$entities = $config->entity_manager->get_entities_by_data(array('username' => $username), array(), user);
		foreach ($entities as $entity) {
			if ( $entity->has_tag('com_user', 'user') )
				return $entity;
		}
		return null;
	}

    /*
	function get_user_tree($mask, $user_array, $selected_id = NULL, $selected = ' selected="selected"', $mark = '') {
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
				$return .= $this->get_user_tree($mask, $user['children'], $selected_id, $selected, $mark.'->');
		}
		return $return;
	}
     */

    /*
	function get_user_menu(&$menu = NULL, $parent_id = NULL, $top_level = TRUE) {
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
     */

    /**
     * Get's a user's username by its GUID.
     *
     * @param int $user_id The user's GUID.
     * @return string|null The username if the user exists, null if it doesn't.
     */
	function get_username($user_id) {
		$entity = $this->get_user($user_id);
        if (is_null($entity)) return null;
		return $entity->username;
	}

    /**
     * Get's an array of users in a group.
     *
     * @param int $group_id The group's GUID.
     * @return array An array of users.
     */
	function get_users_by_group($group_id) {
		global $config;
		$entities = array();
		$entities = $config->entity_manager->get_entities_by_tags('com_user', 'user', user);
        $return = array();
		foreach ($entities as $entity) {
			if ( $entity->ingroup($group_id) )
				$return[] = $entity;
		}
		return $return;
	}

    /**
     * Creates and attaches a module which lists groups.
     */
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

    /**
     * Creates and attaches a module which lists users.
     */
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

    /**
     * Logs the given user into the system.
     *
     * @param int $id The GUID of the user.
     * @return bool True on success, false on failure.
     */
	function login($id) {
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
     * Creates and attaches a module containing a form for editing a group.
     *
     * If $id is null, or not given, a blank form will be provided.
     *
     * @param string $heading The heading for the form.
     * @param string $new_option The option to which the form will submit.
     * @param string $new_action The action to which the form will submit.
     * @param int $id The GUID of the group to edit.
     */
	function print_group_form($heading, $new_option, $new_action, $id = NULL) {
		global $config;
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

    /**
     * Creates and attaches a module containing a form for editing a user.
     *
     * If $id is null, or not given, a blank form will be provided.
     *
     * @param string $heading The heading for the form.
     * @param string $new_option The option to which the form will submit.
     * @param string $new_action The action to which the form will submit.
     * @param int $id The GUID of the user to edit.
     */
	function print_user_form($heading, $new_option, $new_action, $id = NULL) {
		global $config;
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
            $module->group = $user->group;
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
		//$module->content($this->get_user_tree('<option value="#guid#"#selected#>#mark# #name# [#username#]</option>', $this->get_user_array(), $parent));
		//$module->content("</select></label>\n");
	}

    /**
     * Kick the user out of the current page.
     *
     * Note that this method completely terminates execution of the script when
     * it is called. Code after this function is called will not run.
     *
     * @param string $message An optional message to display to the user.
     * @param string $url An option URL to be included in the query data of the redirection url.
     */
	function punt_user($message = NULL, $url = NULL) {
		global $config;
		header("Location: ".$config->template->url('com_user', 'exit', array('message' => urlencode($message), 'url' => urlencode($url)), false));
		exit;
	}
}

?>