<?php
/**
 * Save changes to a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pass = true;

if ( empty($_REQUEST['username']) ) {
	display_error('Must specify username!');
	$pass = false;
}

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_user/edit') && (!gatekeeper('com_user/self') || ($_REQUEST['id'] != $_SESSION['user_id'])) ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_user', 'manageusers', null, false));
	return;
	}
	$user = $config->user_manager->get_user($_REQUEST['id']);
	if ( is_null($user) ) {
	display_error('User doesn\'t exists!');
	$pass = false;
	}
	if ( $user->username != $_REQUEST['username'] ) {
	if ( is_null($config->user_manager->get_user_by_username($_REQUEST['username'])) ) {
		$user->username = $_REQUEST['username'];
	} else {
		display_error('Username ['.$_REQUEST['username'].'] already exists! Continuing with old username...');
	}
	}
	if ( !empty($_REQUEST['password']) ) $user->password($_REQUEST['password']);
} else {
	if ( !gatekeeper('com_user/new') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_user', 'manageusers', null, false));
	return;
	}
	if ( empty($_REQUEST['password']) && !$config->com_user->empty_pw ) {
	display_error('Must specify password!');
	$pass = false;
	}
	$user = new user;
	if ( is_null($config->user_manager->get_user_by_username($_REQUEST['username'])) ) {
	$user->username = $_REQUEST['username'];
	} else {
	display_error('Username already exists!');
	$pass = false;
	}
	$user->password($_REQUEST['password']);
}

$user->name = $_REQUEST['name'];
$user->email = $_REQUEST['email'];

if ( gatekeeper('com_user/default_component') ) {
	if ( file_exists('components/'.$_REQUEST['default_component'].'/actions/default.php') ) {
	$user->default_component = $_REQUEST['default_component'];
	} else {
	display_error('Selected component does not support a default action.');
	}
}

// Go through a list of all groups, and assign them if they're selected.
// Groups that the user does not have access to will not be received from the
// entity manager after com_user filters the result, and thus will not be
// assigned.
if ( gatekeeper("com_user/assigng") ) {
	$groups = $config->entity_manager->get_entities_by_tags('com_user', 'group', group);
	$ugroup = intval($_REQUEST['gid']);
	$ugroups = $_REQUEST['groups'];
	if (is_array($ugroups))
	array_walk($ugroups, 'intval');
	if (is_array($groups)) {
	foreach ($groups as $cur_group) {
		if ( $cur_group->guid == $ugroup ) {
		$user->gid = $ugroup;
		}
		if (is_array($ugroups)) {
		if ( in_array($cur_group->guid, $ugroups) ) {
			$user->addgroup($cur_group->guid);
		} else {
			$user->delgroup($cur_group->guid);
		}
		} else {
		$user->delgroup($cur_group->guid);
		}
	}
	}
	if ( $_REQUEST['gid'] == 'null' ) {
	if (isset($user->gid))
		unset($user->gid);
	}
}

if ( $_REQUEST['abilities'] === 'true' && gatekeeper("com_user/abilities") ) {
	$user->inherit_abilities = ($_REQUEST['inherit_abilities'] == 'ON' ? true : false);
	$sections = array('system');
	foreach ($config->components as $cur_component) {
	$sections[] = $cur_component;
	}
	foreach ($sections as $cur_section) {
	$section_abilities = $config->ability_manager->get_abilities($cur_section);
	if ( count($section_abilities) ) {
		foreach ($section_abilities as $cur_ability) {
		if ( isset($_REQUEST[$cur_section]) && (array_search($cur_ability['ability'], $_REQUEST[$cur_section]) !== false) ) {
			$user->grant($cur_section.'/'.$cur_ability['ability']);
		} else {
			$user->revoke($cur_section.'/'.$cur_ability['ability']);
		}
		}
	}
	}
}

if (!$pass) {
	$module = $config->user_manager->print_user_form('com_user', 'saveuser');
	$module->entity = $user;
	return;
}

if ($user->save()) {
	display_notice('Saved user ['.$user->username.']');
	pines_log('Saved user ['.$user->username.']');
} else {
	display_error('Error saving user. Do you have permission?');
}

$config->user_manager->list_users();
?>