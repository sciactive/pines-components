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

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_user/edituser') && (!gatekeeper('com_user/self') || ($_REQUEST['id'] != $_SESSION['user_id'])) )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listusers', null, false));
	$user = user::factory((int) $_REQUEST['id']);
	if (is_null($user->guid)) {
		display_error('Requested user id is not accessible.');
		return;
	}
	if ( !empty($_REQUEST['password']) )
		$user->password($_REQUEST['password']);
} else {
	if ( !gatekeeper('com_user/newuser') )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listusers', null, false));
	$user = user::factory();
	$user->password($_REQUEST['password']);
}

$user->username = $_REQUEST['username'];
$user->name = $_REQUEST['name'];
$user->email = $_REQUEST['email'];
$user->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
$user->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
$user->timezone = $_REQUEST['timezone'];
// Location
$user->address_type = $_REQUEST['address_type'];
$user->address_1 = $_REQUEST['address_1'];
$user->address_2 = $_REQUEST['address_2'];
$user->city = $_REQUEST['city'];
$user->state = $_REQUEST['state'];
$user->zip = $_REQUEST['zip'];
$user->address_international = $_REQUEST['address_international'];

if ( gatekeeper('com_user/default_component') ) {
	if ( file_exists("components/{$_REQUEST['default_component']}/actions/default.php") ) {
		$user->default_component = $_REQUEST['default_component'];
	} else {
		display_notice('Selected component does not support a default action.');
	}
}

// Go through a list of all groups, and assign them if they're selected.
// Groups that the user does not have access to will not be received from the
// entity manager after com_user filters the result, and thus will not be
// assigned.
if ( gatekeeper("com_user/assigngroup") ) {
	$sys_groups = $config->entity_manager->get_entities(array('tags' => array('com_user', 'group'), 'class' => group));
	$group = group::factory((int) $_REQUEST['group']);
	$groups = $_REQUEST['groups'];
	if (!is_array($groups))
		$groups = array();
	array_walk($groups, 'intval');
	foreach ($sys_groups as $cur_group) {
		if ($cur_group->is($group))
			$user->group = $group;
		if (is_array($groups) && in_array($cur_group->guid, $groups)) {
			$user->addgroup($cur_group);
		} else {
			$user->delgroup($cur_group);
		}
	}
	if ($_REQUEST['group'] == 'null')
		unset($user->group);
}

if ( $_REQUEST['abilities'] === 'true' && gatekeeper("com_user/abilities") ) {
	$user->inherit_abilities = ($_REQUEST['inherit_abilities'] == 'ON');
	$sections = array('system');
	foreach ($config->components as $cur_component) {
		$sections[] = $cur_component;
	}
	foreach ($sections as $cur_section) {
		$section_abilities = $config->ability_manager->get_abilities($cur_section);
		foreach ($section_abilities as $cur_ability) {
			if ( isset($_REQUEST[$cur_section]) && (array_search($cur_ability['ability'], $_REQUEST[$cur_section]) !== false) ) {
				$user->grant($cur_section.'/'.$cur_ability['ability']);
			} else {
				$user->revoke($cur_section.'/'.$cur_ability['ability']);
			}
		}
	}
}

if (empty($user->username)) {
	$user->print_form();
	display_notice('Please specify a username.');
	return;
}
$test = user::factory($_REQUEST['username']);
if (isset($test->guid) && !$user->is($test)) {
	$user->print_form();
	display_notice('There is already a user with that username. Please choose a different username.');
	return;
}
if (empty($user->password) && !$config->com_user->empty_pw) {
	$user->print_form();
	display_notice('Please specify a password.');
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