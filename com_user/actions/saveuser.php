<?php
defined('D_RUN') or die('Direct access prohibited');

if ( empty($_REQUEST['username']) ) {
	display_error('Must specify username!');
	return;
}

if ( isset($_REQUEST['user_id']) ) {
	if ( !gatekeeper('com_user/edit') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'manageusers', null, false));
		return;
	}
	$user = new user;
	$user = $config->user_manager->get_user($_REQUEST['user_id']);
	if ( is_null($user) ) {
		display_error('User doesn\'t exists!');
		return;
	}
	if ( $user->username != $_REQUEST['username'] ) {
		if ( is_null($config->user_manager->get_user_by_username($_REQUEST['username'])) ) {
			$user->username = $_REQUEST['username'];
		} else {
			display_error('Username ['.$_REQUEST['username'].'] already exists! Continuing with old username...');
		}
	}
	if ( !empty($_REQUEST['password']) ) $config->user_manager->password($user, $_REQUEST['password']);
} else {
	if ( !gatekeeper('com_user/new') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'manageusers', null, false));
		return;
	}
	if ( empty($_REQUEST['password']) && !$config->com_user->empty_pw ) {
		display_error('Must specify password!');
		return;
	}
	$user = new user;
	$user = $config->user_manager->new_user();
	if ( is_null($config->user_manager->get_user_by_username($_REQUEST['username'])) ) {
		$user->username = $_REQUEST['username'];
	} else {
		display_error('Username already exists!');
		return;
	}
	$config->user_manager->password($user, $_REQUEST['password']);
}

$user->name = $_REQUEST['name'];
$user->email = $_REQUEST['email'];
/*if ( $_REQUEST['parent'] == 'none' ) {
	$parent = NULL;
} else {
	if ( !is_null($config->user_manager->get_user($_REQUEST['parent'])) && $_REQUEST['parent'] !== $user->guid ) {
		$parent = $_REQUEST['parent'];
	} else {
		display_error('Parent is not valid!');
		return;
	}
}
$user->parent = $parent; */

if ( $_REQUEST['abilities'] === 'true' && gatekeeper("com_user/abilities") ) {
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

$user->save();

display_notice('Saved user ['.$user->username.']');

$config->user_manager->list_users();
?>