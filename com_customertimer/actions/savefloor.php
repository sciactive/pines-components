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
		punt_user(null, pines_url('com_customertimer', 'listfloors'));
	$floor = com_customertimer_floor::factory((int) $_REQUEST['id']);
	if (!isset($floor->guid)) {
		pines_error('Requested floor id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_customertimer/newfloor') )
		punt_user(null, pines_url('com_customertimer', 'listfloors'));
	$floor = com_customertimer_floor::factory();
}

// General
$floor->name = $_REQUEST['name'];
$floor->enabled = ($_REQUEST['enabled'] == 'ON');
$floor->description = $_REQUEST['description'];
if ($pines->uploader->check($_REQUEST['background']))
	$floor->background = $_REQUEST['background'];

// Station Layout
$floor->stations = json_decode($_REQUEST['stations'], true);

$test = $pines->entity_manager->get_entity(array('class' => com_customertimer_floor, 'skip_ac' => true), array('&', 'data' => array('name', $floor->name), 'tag' => array('com_customertimer', 'floor')));
if (isset($test) && !$floor->is($test)) {
	$floor->print_form();
	pines_notice('There is already a floor with that name. Please choose a different name.');
	return;
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