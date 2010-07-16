<?php
/**
 * Save changes to a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_user/edituser') && (!gatekeeper('com_user/self') || ($_REQUEST['id'] != $_SESSION['user_id'])) )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listusers'));
	$user = user::factory((int) $_REQUEST['id']);
	if (!isset($user->guid)) {
		pines_error('Requested user id is not accessible.');
		return;
	}
	if ( !empty($_REQUEST['password']) )
		$user->password($_REQUEST['password']);
} else {
	if ( !gatekeeper('com_user/newuser') )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listusers'));
	$user = user::factory();
	$user->password($_REQUEST['password']);
}

$user->username = $_REQUEST['username'];
$user->name = $_REQUEST['name'];
$user->enabled = ($_REQUEST['enabled'] == 'ON');
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
$user->addresses = (array) json_decode($_REQUEST['addresses']);
foreach ($user->addresses as &$cur_address) {
	$array = array(
		'type' => $cur_address->values[0],
		'address_1' => $cur_address->values[1],
		'address_2' => $cur_address->values[2],
		'city' => $cur_address->values[3],
		'state' => $cur_address->values[4],
		'zip' => $cur_address->values[5]
	);
	$cur_address = $array;
}
unset($cur_address);

if (gatekeeper('com_user/assignpin'))
	$user->pin = $_REQUEST['pin'];

// Attributes
$user->attributes = (array) json_decode($_REQUEST['attributes']);
foreach ($user->attributes as &$cur_attribute) {
	$array = array(
		'name' => $cur_attribute->values[0],
		'value' => $cur_attribute->values[1]
	);
	$cur_attribute = $array;
}
unset($cur_attribute);

// Go through a list of all groups, and assign them if they're selected.
// Groups that the user does not have access to will not be received from the
// entity manager after com_user filters the result, and thus will not be
// assigned.
if ( gatekeeper('com_user/assigngroup') ) {
	$highest_primary_parent = $pines->config->com_user->highest_primary;
	$highest_secondary_parent = $pines->config->com_user->highest_secondary;
	$primary_groups = $secondary_groups = array();
	if ($highest_primary_parent == 0) {
		$primary_groups = $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group')));
	} else {
		if ($highest_primary_parent > 0) {
			$highest_primary_parent = group::factory($highest_primary_parent);
			if (isset($highest_primary_parent->guid))
				$primary_groups = $highest_primary_parent->get_descendents();
		}
	}
	if ($highest_secondary_parent == 0) {
		$secondary_groups = $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group')));
	} else {
		if ($highest_secondary_parent > 0) {
			$highest_secondary_parent = group::factory($highest_secondary_parent);
			if (isset($highest_secondary_parent->guid))
				$secondary_groups = $highest_secondary_parent->get_descendents();
		}
	}
	$group = group::factory((int) $_REQUEST['group']);
	$groups = array_map('intval', (array) $_REQUEST['groups']);
	foreach ($primary_groups as $cur_group) {
		if ($cur_group->is($group)) {
			$user->group = $group;
			break;
		}
	}
	foreach ($secondary_groups as $cur_group) {
		if (in_array($cur_group->guid, $groups)) {
			$user->add_group($cur_group);
		} else {
			$user->del_group($cur_group);
		}
	}
	// What if the user can't assign the current primary group, so it defaults to null?
	//if ($_REQUEST['group'] == 'null' || $_REQUEST['no_primary_group'] == 'ON' )
	if ($_REQUEST['group'] == 'null')
		unset($user->group);
}

if ( gatekeeper('com_user/abilities') ) {
	$user->inherit_abilities = ($_REQUEST['inherit_abilities'] == 'ON');
	$sections = array('system');
	foreach ($pines->components as $cur_component) {
		$sections[] = $cur_component;
	}
	foreach ($sections as $cur_section) {
		if ($cur_section == 'system') {
			$section_abilities = (array) $pines->info->abilities;
		} else {
			$section_abilities = (array) $pines->info->$cur_section->abilities;
		}
		foreach ($section_abilities as $cur_ability) {
			if ( isset($_REQUEST[$cur_section]) && (array_search($cur_ability[0], $_REQUEST[$cur_section]) !== false) ) {
				$user->grant($cur_section.'/'.$cur_ability[0]);
			} else {
				$user->revoke($cur_section.'/'.$cur_ability[0]);
			}
		}
	}
}

if (empty($user->username)) {
	$user->print_form();
	pines_notice('Please specify a username.');
	return;
}
if ($pines->config->com_user->max_username_length > 0 && strlen($user->username) > $pines->config->com_user->max_username_length) {
	$user->print_form();
	pines_notice("Usernames must not exceed {$pines->config->com_user->max_username_length} characters.");
	return;
}
$test = user::factory($_REQUEST['username']);
if (isset($test->guid) && !$user->is($test)) {
	$user->print_form();
	pines_notice('There is already a user with that username. Please choose a different username.');
	return;
}
if (empty($user->password) && !$pines->config->com_user->empty_pw) {
	$user->print_form();
	pines_notice('Please specify a password.');
	return;
}
if (gatekeeper('com_user/assignpin') && !empty($user->pin)) {
	$test = $pines->entity_manager->get_entity(array('class' => user), array('&', 'data' => array('pin', $user->pin), 'tag' => array('com_user', 'user')));
	if (isset($test) && !$user->is($test)) {
		$user->print_form();
		pines_notice('This PIN is already in use.');
		return;
	}

	if ($pines->config->com_user->min_pin_length > 0 && strlen($user->pin) < $pines->config->com_user->min_pin_length) {
		$user->print_form();
		pines_notice("User PINs must be at least {$pines->config->com_user->min_pin_length} characters.");
		return;
	}
}
if ($user->save()) {
	pines_notice('Saved user ['.$user->username.']');
	pines_log('Saved user ['.$user->username.']');
} else {
	pines_error('Error saving user. Do you have permission?');
}

if ($user->enabled) {
	redirect(pines_url('com_user', 'listusers'));
} else {
	redirect(pines_url('com_user', 'listusers', array('enabled' => 'false')));
}

?>