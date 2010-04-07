<?php
/**
 * Save changes to a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_user/editgroup') )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listgroups'));
	$group = group::factory((int) $_REQUEST['id']);
	if (is_null($group->guid)) {
		pines_error('Requested group id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_user/newgroup') )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listgroups'));
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

/**
 * @todo Check if the selected parent is a descendant of this group.
 */
// Clean the requested parent. Make sure it's both valid and not the same group.
if ( $_REQUEST['parent'] == 'none' ) {
	unset($group->parent);
} else {
	$group->parent = group::factory((int) $_REQUEST['parent']);
}

if ( $_REQUEST['abilities'] === 'true' && gatekeeper('com_user/abilities') ) {
	$sections = array('system');
	foreach ($pines->components as $cur_component) {
		$sections[] = $cur_component;
	}
	foreach ($sections as $cur_section) {
		$section_abilities = $pines->ability_manager->get_abilities($cur_section);
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
	pines_notice('Please specify a groupname.');
	return;
}
if ($pines->com_user->max_groupname_length > 0 && strlen($group->groupname) > $pines->com_user->max_groupname_length) {
	$group->print_form();
	pines_notice("Groupnames must not exceed {$pines->com_user->max_groupname_length} characters.");
	return;
}
$test = group::factory($_REQUEST['groupname']);
if (isset($test->guid) && !$group->is($test)) {
	$group->print_form();
	pines_notice('There is already a group with that groupname. Please choose a different groupname.');
	return;
}
if (isset($group->parent) && (is_null($group->parent->guid) || $group->is($group->parent))) {
	$group->print_form();
	pines_notice('Parent group is not valid.');
	return;
}

// Logo image upload and resizing.
$image = $_FILES['image'];
if (!empty($image['name'])) {
	if ($image['size'] > 205000) {
		$group->print_form();
		pines_notice('Images cannot exceed 200KB.');
		return;
	}
	if ($image['error'] > 0) {
		$group->print_form();
		pines_error("Image Error: {$image['error']}");
		return;
	}
	if (!in_array($image['type'], array('image/jpeg', 'image/png', 'image/gif'))) {
		$group->print_form();
		pines_notice('Acceptable image types are jpg, png, and gif.');
		return;
	}
	if (!isset($group->guid) && !$group->save()) {
		$group->print_form();
		pines_error('Error saving group.');
		return;
	}
	// Resize and create the image with the Pines logo naming scheme.
	if (isset($group->logo) && file_exists("{$pines->config->setting_upload}logos/{$group->logo}"))
		unlink("{$pines->config->setting_upload}logos/{$group->logo}");
	switch ($image['type']) {
		case 'image/jpeg':
			$group->logo = "{$group->guid}_logo.jpg";
			if ($pines->config->com_user->resize_logos) {
				$img_raw = imagecreatefromjpeg($image['tmp_name']);
				$currwidth = imagesx($img_raw);
				$currheight = imagesy($img_raw);
				$img_resized = imagecreate($pines->config->com_user->logo_width, $pines->config->com_user->logo_height);
				imagecopyresized($img_resized, $img_raw, 0, 0, 0, 0, $pines->config->com_user->logo_width, $pines->config->com_user->logo_height, $currwidth, $currheight);
				imagejpeg($img_resized, "{$pines->config->setting_upload}logos/{$group->logo}");
				imagedestroy($img_raw);
				imagedestroy($img_resized);
			} else {
				move_uploaded_file($image['tmp_name'], "{$pines->config->setting_upload}logos/{$group->logo}");
			}
			break;
		case 'image/png':
			$group->logo = "{$group->guid}_logo.png";
			if ($pines->config->com_user->resize_logos) {
				$img_raw = imagecreatefrompng($image['tmp_name']);
				$currwidth = imagesx($img_raw);
				$currheight = imagesy($img_raw);
				$img_resized = imagecreate($pines->config->com_user->logo_width, $pines->config->com_user->logo_height);
				imagecopyresized($img_resized, $img_raw, 0, 0, 0, 0, $pines->config->com_user->logo_width, $pines->config->com_user->logo_height, $currwidth, $currheight);
				imagepng($img_resized, "{$pines->config->setting_upload}logos/{$group->logo}");
				imagedestroy($img_raw);
				imagedestroy($img_resized);
			} else {
				move_uploaded_file($image['tmp_name'], "{$pines->config->setting_upload}logos/{$group->logo}");
			}
			break;
		case 'image/gif':
			$group->logo = "{$group->guid}_logo.gif";
			if ($pines->config->com_user->resize_logos) {
				$img_raw = imagecreatefromgif($image['tmp_name']);
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
				imagegif($img_resized, "{$pines->config->setting_upload}logos/{$group->logo}");
				imagedestroy($img_raw);
				imagedestroy($img_resized);
			} else {
				move_uploaded_file($image['tmp_name'], "{$pines->config->setting_upload}logos/{$group->logo}");
			}
			break;
	}
} else if ($_REQUEST['remove_logo'] == 'ON' && isset($group->logo)) {
	if (file_exists("{$pines->config->setting_upload}logos/{$group->logo}"))
		unlink("{$pines->config->setting_upload}logos/{$group->logo}");
	unset($group->logo);
}

if ($group->save()) {
	pines_notice('Saved group ['.$group->groupname.']');
	pines_log('Saved group ['.$group->groupname.']');
} else {
	pines_error('Error saving group. Do you have permission?');
}

$pines->user_manager->list_groups();
?>