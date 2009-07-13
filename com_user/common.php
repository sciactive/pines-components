<?php
defined('D_RUN') or die('Direct access prohibited');

class com_user {
	function authenticate($username, $password) {
		$entity = new entity;
		$entity = $this->get_user_by_username($username);
		if ( $entity->password === md5($password.$entity->salt) ) {
			return $entity->guid;
		} else {
			return null;
		}
	}

	function delete_user($user_id) {
		// TODO: delete children
		$entity = new entity;
		if ( $entity = $this->get_user($user_id) ) {
			$entity->delete();
			return true;
		} else {
			return false;
		}
	}

	function gatekeeper($ability = NULL, $user = NULL) {
		if ( is_null($user) ) {
			if ( isset($_SESSION['user']) ) {
				$user = $_SESSION['user'];
			} else {
				unset($user);
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

	function get_user($user_id) {
		global $config;
		$entity = new entity;
		$entity = $config->entity_manager->get_entity($user_id);
		if ( empty($entity) )
			return null;
		
		if ( $entity->has_tag('com_user', 'user') ) {
			return $entity;
		} else {
			return null;
		}
	}

	function get_user_array($parent_id = NULL) {
		// TODO: check for orphans, they could cause users to be hidden
		global $config;
		$return = array();
		if ( is_null($parent_id) ) {
			$entities = $config->entity_manager->get_entities_by_tags('com_user', 'user');
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
			$entities = $config->entity_manager->get_entities_by_parent($parent_id);
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
		$entity = new entity;
		$entities = $config->entity_manager->get_entities_by_data(array('username' => $username));
		foreach ($entities as $entity) {
			if ( $entity->has_tag('com_user', 'user') )
				return $entity;
		}
		return null;
	}

	function get_user_menu($parent_id = NULL, &$menu = NULL, $menu_parent = NULL, $top_level = TRUE) {
		global $config;
		if ( is_null($parent_id) ) {
			$entities = $config->entity_manager->get_entities_by_tags('com_user', 'user');
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
		$entity = new entity;
		$entity = $this->get_user($user_id);
		return $entity->username;
	}

	/*
	 * Abilities should be named following this form!!
	 *     com_componentname/abilityname
	 * If it is a system ability (ie. not part of a component, substitute
	 * "com_componentname" with "system". The system ability "all" means the user
	 * has every ability available.
	 */
	function grant($user_abilities, $ability) {
		if ( !in_array($ability, $user_abilities) )
			array_push($user_abilities, $ability);
		return $user_abilities;
	}

	function list_users($line_header, $line_footer) {
		global $config;
		/*$entities = array();
		$entity = new entity; */

		/* TODO: Remove after testing with left and right modules. */
		$module = new module('system', 'false', 'left');
		$module->title = "Left Users";
		$module->content("No users here ;)<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />");

		$module = new module('system', 'false', 'right');
		$module->title = "Right Users";
		$module->content("No users here ;)");
		/* End remove. */

		$module = new module('com_user', 'list_users', 'content');
		$module->title = "Users";

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

		/*
		$entities = $config->entity_manager->get_entities_by_tags('com_user', 'user');

		foreach($entities as $entity) {
			$cur_user = $entity->username;
			$cur_user_id = $entity->guid;
			$module->content($line_header . "<strong>$cur_user</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			$module->content("<input type=\"button\" onclick=\"window.location='".$config->template->url('com_user', 'edituser', array('user_id' => urlencode($cur_user_id)))."';\" value=\"Edit\" /> | ");
			$module->content("<input type=\"button\" onclick=\"if(confirm('Are you sure you want to delete \\'$cur_user\\'?')) {window.location='".$config->template->url('com_user', 'deleteuser', array('user_id' => urlencode($cur_user_id)))."';}\" value=\"Delete\" />");
			$module->content($line_footer . "<br /><br />\n");
		}

		if ( empty($entities) )
			display_notice("There are no users.");
		 */
	}

	function login($id) {
		$entity = new entity;

		$entity = $this->get_user($id);

		if ( isset($entity->username) ) {
			if ( $this->gatekeeper('com_user/login', $entity) ) {
				$_SESSION['user_id'] = $entity->guid;
				$_SESSION['user'] = $entity;
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

	function new_user() {
		$new_user = new entity;
		$new_user->add_tag('com_user', 'user');
		$new_user->salt = md5(rand());
		$new_user->abilities = array();
		return $new_user;
	}

	function password(&$user, $password) {
		$user->password = md5($password.$user->salt);
	}

	function print_login() {
		$module = new module('com_user', 'login', 'content');
	}

	function print_user_form($heading, $new_action, $id = NULL) {
		global $config, $page;
		$page->head("<script type=\"text/javascript\" src=\"components/com_user/js/verify.js\"></script>\n");
		$module = new module('com_user', 'user_form', 'content');
		$module->content("<form method=\"post\" id=\"user_details\" action=\"\" onsubmit=\"return verify_form('user_details');\">\n");
		$module->content("<div class=\"stylized stdform\">");
		$module->content("<h2>$heading</h2>\n");
		$module->content("<p>Provide user details in this form.</p>\n");
		if ( is_null($id) ) {
			$username = $name = '';
			$user_abilities = array();
		} else {
			$user = $this->get_user($id);
			$username = $user->username;
			$name = $user->name;
			$email = $user->email;
			$parent = $user->parent;
			$user_abilities = $user->abilities;
			$module->content("<input type=\"hidden\" name=\"user_id\" value=\"$id\" />\n");
		}
		$module->content("<label>Username<input type=\"text\" name=\"username\" value=\"$username\" /></label>\n");
		$module->content("<label>Name<input type=\"text\" name=\"name\" value=\"$name\" /></label>\n");
		$module->content("<label>Email<input type=\"text\" name=\"email\" value=\"$email\" /></label>\n");
		$module->content(is_null($id) ? "<label>Password<span class=\"small\">".($config->com_user->empty_pw ? "May be blank." : "&nbsp;")."</span>" : "<label>Update Password<span class=\"small\">Leave blank, if not changing.</span>");
		$module->content("<input type=\"password\" name=\"password\" /></label>\n");
		$module->content("<label>Repeat Password<input type=\"password\" name=\"password2\" /></label>\n");
		$module->content("<label>Parent<select name=\"parent\">\n");
		$module->content("<option value=\"none\">--No Parent--</option>\n");
		$module->content($this->print_user_tree('<option value="#guid#"#selected#>#mark# #name# [#username#]</option>', $this->get_user_array(), $parent));
		$module->content("</select></label>\n");
		if ( gatekeeper("com_user/abilities") ) {
			$module->content("<input type=\"hidden\" name=\"abilities\" value=\"true\" />\n");
			$module->content("<label>Abilities</label><br />\n");
			$sections = array('system');
			foreach ($config->components as $cur_component) {
				$sections[] = $cur_component;
			}
			foreach ($sections as $cur_section) {
				$section_abilities = $config->ability_manager->get_abilities($cur_section);
				if ( count($section_abilities) ) {
					$module->content("<table width=\"100%\">\n<thead><tr><th colspan=\"2\">$cur_section</th></tr></thead>\n<tbody>\n");
					foreach ($section_abilities as $cur_ability) {
						$module->content('<tr><td><label><input type="checkbox" name="'.$cur_section.'[]" value="'.$cur_ability['ability'].'"');
						if ( array_search($cur_section.'/'.$cur_ability['ability'], $user_abilities) !== false ) {
							$module->content(' checked');
						}
						$module->content(' />&nbsp;'.$cur_ability['title'].'</label></td><td style="width: 80%;">'.$cur_ability['description']."</td></tr>\n");
					}
					$module->content("</tbody>\n</table>\n");
				}
			}
		}
		$module->content("<input type=\"hidden\" name=\"action\" value=\"$new_action\" />\n");
		$module->content("<input type=\"submit\" value=\"Submit\" />\n");
		$module->content("<input type=\"button\" onclick=\"window.location='".$config->template->url('com_user', 'manageusers')."';\" value=\"Cancel\" />\n");
		$module->content("<div class=\"spacer\"></div>\n");
		$module->content("</div>\n");
		$module->content("</form>\n");
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

	function revoke($user_abilities, $ability) {
		if ( in_array($ability, $user_abilities) )
			unset($user_abilities[array_search($ability, $user_abilities)]);
		return $user_abilities;
	}
}

$config->user_manager = new com_user;
$config->ability_manager->add('com_user', 'login', 'Login', 'User can login to the system. (Useful for making user categories.)');
$config->ability_manager->add('com_user', 'new', 'Create Users', 'Let user create new users.');
$config->ability_manager->add('com_user', 'manage', 'Manage Users', 'Let user see and manage other users. Required to access the below abilities.');
$config->ability_manager->add('com_user', 'edit', 'Edit Users', 'Let user edit other users\' details.');
$config->ability_manager->add('com_user', 'delete', 'Delete Users', 'Let user delete other users.');
$config->ability_manager->add('com_user', 'abilities', 'Manage Abilities', 'Let user manage other users\' and his own abilities.');

if ( isset($_SESSION['user_id']) )
	$_SESSION['user'] = $config->user_manager->get_user($_SESSION['user_id']);

/*
 * This is a shortcut for a very commonly used function. Any user management
 * component should provide a shortcut for gatekeeper.
 */
function gatekeeper($ability = NULL) {
	global $config;
	return $config->user_manager->gatekeeper($ability);
}

?>