<?php
/**
 * Save changes to a floor.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_customertimer/editfloor') )
		punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'listfloors'));
	$floor = com_customertimer_floor::factory((int) $_REQUEST['id']);
	if (!isset($floor->guid)) {
		pines_error('Requested floor id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_customertimer/newfloor') )
		punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'listfloors'));
	$floor = com_customertimer_floor::factory();
}

// General
$floor->name = $_REQUEST['name'];
$floor->enabled = ($_REQUEST['enabled'] == 'ON');
$floor->description = $_REQUEST['description'];

// Station Layout
$floor->stations = json_decode($_REQUEST['stations'], true);

$test = $pines->entity_manager->get_entity(array('data' => array('name' => $floor->name), 'tags' => array('com_customertimer', 'floor'), 'class' => com_customertimer_floor));
if (isset($test) && !$floor->is($test)) {
	$floor->print_form();
	pines_notice('There is already a floor with that name. Please choose a different name.');
	return;
}

// Background image upload and resizing.
$image = $_FILES['background'];
if (!empty($image['name'])) {
	if ($image['error'] > 0) {
		$floor->print_form();
		pines_error("Image Error: {$image['error']}");
		return;
	}
	if (!in_array($image['type'], array('image/jpeg', 'image/png', 'image/gif'))) {
		$floor->print_form();
		pines_notice('Acceptable image types are jpg, png, and gif.');
		return;
	}
	if (!isset($floor->guid) && !$floor->save()) {
		$floor->print_form();
		pines_error('Error saving floor.');
		return;
	}
	if (isset($floor->background) && file_exists("{$pines->config->upload_location}floorplans/{$floor->background}"))
		unlink("{$pines->config->upload_location}floorplans/{$floor->background}");
	$floor->background = clean_filename($image['name']);
	move_uploaded_file($image['tmp_name'], "{$pines->config->upload_location}floorplans/{$floor->background}");
} else if ($_REQUEST['remove_background'] == 'ON' && isset($floor->background)) {
	if (file_exists("{$pines->config->upload_location}floorplans/{$floor->background}"))
		unlink("{$pines->config->upload_location}floorplans/{$floor->background}");
	unset($floor->background);
}

if ($pines->config->com_customertimer->global_floors)
	$floor->ac->other = 1;

if ($floor->save()) {
	pines_notice('Saved floor ['.$floor->name.']');
} else {
	pines_error('Error saving floor. Do you have permission?');
}

redirect(pines_url('com_customertimer', 'listfloors'));

?>