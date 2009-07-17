<?php
/**
 * Save changes to a group.
 *
 * @package XROOM
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

if ( empty($_REQUEST['groupname']) ) {
	display_error('Must specify groupname!');
	return;
}

if ( isset($_REQUEST['group_id']) ) {
	if ( !gatekeeper('com_user/editg') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'managegroups', null, false));
		return;
	}
	$group = new group;
	$group = $config->user_manager->get_group($_REQUEST['group_id']);
	if ( is_null($group) ) {
		display_error('Group doesn\'t exists!');
		return;
	}
	if ( $group->groupname != $_REQUEST['groupname'] ) {
		if ( is_null($config->user_manager->get_group_by_groupname($_REQUEST['groupname'])) ) {
			$group->groupname = $_REQUEST['groupname'];
		} else {
			display_error('Groupname ['.$_REQUEST['groupname'].'] already exists! Continuing with old groupname...');
		}
	}
} else {
	if ( !gatekeeper('com_user/newg') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'managegroups', null, false));
		return;
	}
	$group = new group;
	$group = $config->user_manager->new_group();
	if ( is_null($config->user_manager->get_group_by_groupname($_REQUEST['groupname'])) ) {
		$group->groupname = $_REQUEST['groupname'];
	} else {
		display_error('Groupname already exists!');
		return;
	}
}

$group->name = $_REQUEST['name'];
$group->email = $_REQUEST['email'];

/**
 * @todo Check if the selected parent is a child of this group.
 */
// Clean the requested parent. Make sure it's both valid and not the same group.
if ( $_REQUEST['parent'] == 'none' ) {
	$parent = NULL;
} else {
	if ( is_null($config->user_manager->get_group($_REQUEST['parent'])) || $_REQUEST['parent'] == $group->guid ) {
		display_error('Parent is not valid!');
		return;
	} else {
		$parent = $_REQUEST['parent'];
	}
}
$group->parent = $parent;

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

$group->save();

display_notice('Saved group ['.$group->groupname.']');

$config->user_manager->list_groups();
?>