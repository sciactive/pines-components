<?php
/**
 * Save changes to a group.
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
	if ( !gatekeeper('com_user/editgroup') )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listgroups', null, false));
	$group = group::factory((int) $_REQUEST['id']);
	if (is_null($group->guid)) {
		display_error('Requested group id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_user/newgroup') )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listgroups', null, false));
	$group = group::factory();
}

$group->groupname = $_REQUEST['groupname'];
$group->name = $_REQUEST['name'];
$group->email = $_REQUEST['email'];
$group->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
$group->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
$group->timezone = $_REQUEST['timezone'];
// Location
$group->address_type = $_REQUEST['address_type'];
$group->address_1 = $_REQUEST['address_1'];
$group->address_2 = $_REQUEST['address_2'];
$group->city = $_REQUEST['city'];
$group->state = $_REQUEST['state'];
$group->zip = $_REQUEST['zip'];
$group->address_international = $_REQUEST['address_international'];

/**
 * @todo Check if the selected parent is a descendant of this group.
 */
// Clean the requested parent. Make sure it's both valid and not the same group.
if ( $_REQUEST['parent'] == 'none' ) {
	$group->parent = NULL;
} else {
	$group->parent = group::factory((int) $_REQUEST['parent']);
}

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
					$group->grant($cur_section.'/'.$cur_ability['ability']);
				} else {
					$group->revoke($cur_section.'/'.$cur_ability['ability']);
				}
			}
		}
	}
}

if (empty($group->groupname)) {
	$group->print_form();
	display_notice('Please specify a groupname.');
	return;
}
$test = group::factory($_REQUEST['groupname']);
if (isset($test->guid) && !$group->is($test)) {
	$group->print_form();
	display_notice('There is already a group with that groupname. Please choose a different groupname.');
	return;
}
if (isset($group->parent) && (is_null($parent->guid) || $group->is($parent))) {
	$group->print_form();
	display_notice('Parent group is not valid.');
	return;
}

if ($group->save()) {
	display_notice('Saved group ['.$group->groupname.']');
	pines_log('Saved group ['.$group->groupname.']');
} else {
	display_error('Error saving group. Do you have permission?');
}

$config->user_manager->list_groups();
?>