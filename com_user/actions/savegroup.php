<?php
/**
 * Save changes to a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_user/editgroup') )
		punt_user(null, pines_url('com_user', 'listgroups'));
	$group = group::factory((int) $_REQUEST['id']);
	if (!isset($group->guid)) {
		pines_error('Requested group id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_user/newgroup') )
		punt_user(null, pines_url('com_user', 'listgroups'));
	$group = group::factory();
}

if (gatekeeper('com_user/usernames'))
	$group->groupname = $_REQUEST['groupname'];
$group->name = $_REQUEST['name'];
if (gatekeeper('com_user/enabling')) {
	if ($_REQUEST['enabled'] == 'ON')
		$group->add_tag('enabled');
	else
		$group->remove_tag('enabled');
}
$group->email = $_REQUEST['email'];
$group->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
$group->phone2 = preg_replace('/\D/', '', $_REQUEST['phone2']);
$group->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
$group->timezone = $_REQUEST['timezone'];
if (gatekeeper('com_user/defaultgroups')) {
	$group->default_primary = $_REQUEST['default_primary'] == 'ON';
	$group->default_secondary = $_REQUEST['default_secondary'] == 'ON';
}
// Location
$group->address_type = $_REQUEST['address_type'];
$group->address_1 = $_REQUEST['address_1'];
$group->address_2 = $_REQUEST['address_2'];
$group->city = $_REQUEST['city'];
$group->state = $_REQUEST['state'];
$group->zip = $_REQUEST['zip'];
$group->address_international = $_REQUEST['address_international'];

// Conditions
if ( gatekeeper('com_user/conditions') ) {
	$conditions = (array) json_decode($_REQUEST['conditions']);
	$group->conditions = array();
	foreach ($conditions as $cur_condition) {
		if (!isset($cur_condition->values[0], $cur_condition->values[1]))
			continue;
		$group->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
	}
}

// Attributes
$group->attributes = (array) json_decode($_REQUEST['attributes']);
foreach ($group->attributes as &$cur_attribute) {
	$array = array(
		'name' => $cur_attribute->values[0],
		'value' => $cur_attribute->values[1]
	);
	$cur_attribute = $array;
}
unset($cur_attribute);

//if ( $_REQUEST['no_parent'] == 'ON' ) {
if ( $_REQUEST['parent'] == 'none' ) {
	unset($group->parent);
} else {
	$parent = group::factory((int) $_REQUEST['parent']);
	// Check if the selected parent is a descendent of this group.
	if (!$group->is($parent) && !$parent->is_descendent($group))
		$group->parent = $parent;
}

if ( gatekeeper('com_user/abilities') ) {
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
				$group->grant($cur_section.'/'.$cur_ability[0]);
			} else {
				$group->revoke($cur_section.'/'.$cur_ability[0]);
			}
		}
	}
}

if (empty($group->groupname)) {
	$group->print_form();
	pines_notice('Please specify a groupname.');
	return;
}
if ($pines->config->com_user->max_groupname_length > 0 && strlen($group->groupname) > $pines->config->com_user->max_groupname_length) {
	$group->print_form();
	pines_notice("Groupnames must not exceed {$pines->config->com_user->max_groupname_length} characters.");
	return;
}
$test = group::factory($_REQUEST['groupname']);
if (isset($test->guid) && !$group->is($test)) {
	$group->print_form();
	pines_notice('There is already a group with that groupname. Please choose a different groupname.');
	return;
}
if (array_diff(str_split($group->groupname), str_split($pines->config->com_user->valid_chars))) {
	$group->print_form();
	pines_notice($pines->config->com_user->valid_chars_notice);
	return;
}
if (!preg_match($pines->config->com_user->valid_regex, $group->groupname)) {
	$group->print_form();
	pines_notice($pines->config->com_user->valid_regex_notice);
	return;
}
if (!empty($group->email)) {
	$test = $pines->entity_manager->get_entity(array('class' => group, 'skip_ac' => true), array('&', 'tag' => array('com_user', 'group'), 'strict' => array('email', $group->email)));
	if (isset($test) && !$group->is($test)) {
		$group->print_form();
		pines_notice('There is already a group with that email address. Please use a different email.');
		return;
	}
}
if (isset($group->parent) && !isset($group->parent->guid)) {
	$group->print_form();
	pines_notice('Parent group is not valid.');
	return;
}
if (gatekeeper('com_user/defaultgroups') && $group->default_primary) {
	$current_primary = $pines->entity_manager->get_entity(array('class' => group), array('&', 'tag' => array('com_user', 'group'), 'data' => array('default_primary', true)));
	if (isset($current_primary) && !$group->is($current_primary)) {
		unset($current_primary->default_primary);
		if ($current_primary->save()) {
			pines_notice("New user primary group changed from {$current_primary->groupname} to {$group->groupname}");
		} else {
			$group->print_form();
			pines_error("Could not change new user primary group from {$current_primary->groupname}.");
			return;
		}
	}
}

if ($_REQUEST['remove_logo'] == 'ON' && isset($group->logo))
	unset($group->logo);

// Logo image upload and resizing.
if (!empty($_REQUEST['image']) && $pines->uploader->check($_REQUEST['image'])) {
	$group->logo = $_REQUEST['image'];
	/* How to resize images without overwriting them?
	if ($pines->config->com_user->resize_logos) {
		// if jpeg
		case 'image/jpeg':
			$img_raw = imagecreatefromjpeg($group->logo);
			$currwidth = imagesx($img_raw);
			$currheight = imagesy($img_raw);
			$img_resized = imagecreate($pines->config->com_user->logo_width, $pines->config->com_user->logo_height);
			imagecopyresized($img_resized, $img_raw, 0, 0, 0, 0, $pines->config->com_user->logo_width, $pines->config->com_user->logo_height, $currwidth, $currheight);
			imagejpeg($img_resized, $group->logo);
			imagedestroy($img_raw);
			imagedestroy($img_resized);
			break;
		// if png
		case 'image/png':
			$img_raw = imagecreatefrompng($group->logo);
			$currwidth = imagesx($img_raw);
			$currheight = imagesy($img_raw);
			$img_resized = imagecreate($pines->config->com_user->logo_width, $pines->config->com_user->logo_height);
			imagecopyresized($img_resized, $img_raw, 0, 0, 0, 0, $pines->config->com_user->logo_width, $pines->config->com_user->logo_height, $currwidth, $currheight);
			imagepng($img_resized, $group->logo);
			imagedestroy($img_raw);
			imagedestroy($img_resized);
			break;
		// if gif
		case 'image/gif':
			$img_raw = imagecreatefromgif($group->logo);
			$currwidth = imagesx($img_raw);
			$currheight = imagesy($img_raw);
			$img_resized = imagecreatetruecolor($pines->config->com_user->logo_width, $pines->config->com_user->logo_height);
			$blank = imagecolortransparent($img_raw);
			// If the image has alpha values (transparency) fill our resized image with blank space.
			if( $blank >= 0 && $blank < imagecolorstotal($img_raw) ) {
				$trans = imagecolorsforindex($img_raw, $blank);
				$trans_color = imagecolorallocate($img_resized, $trans['red'], $trans['green'], $trans['blue']);
				imagefill( $img_resized, 0, 0, $trans_color );
				imagecolortransparent( $img_resized, $trans_color );
			}
			imagecopyresized($img_resized, $img_raw, 0, 0, 0, 0, $pines->config->com_user->logo_width, $pines->config->com_user->logo_height, $currwidth, $currheight);
			imagegif($img_resized, $group->logo);
			imagedestroy($img_raw);
			imagedestroy($img_resized);
			break;
	}
	*/
}

if ($group->save()) {
	pines_notice('Saved group ['.$group->groupname.']');
	pines_log('Saved group ['.$group->groupname.']');
} else {
	pines_error('Error saving group. Do you have permission?');
}

if ($group->has_tag('enabled')) {
	pines_redirect(pines_url('com_user', 'listgroups'));
} else {
	pines_redirect(pines_url('com_user', 'listgroups', array('enabled' => 'false')));
}

?>