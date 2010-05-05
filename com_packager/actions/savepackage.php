<?php
/**
 * Save changes to a package.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_packager/editpackage') )
		punt_user('You don\'t have necessary permission.', pines_url('com_packager', 'listpackages'));
	$package = com_packager_package::factory((int) $_REQUEST['id']);
	if (!isset($package->guid)) {
		pines_error('Requested package id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_packager/newpackage') )
		punt_user('You don\'t have necessary permission.', pines_url('com_packager', 'listpackages'));
	$package = com_packager_package::factory();
}

// General
$package->name = preg_replace('/[^a-z0-9_-]/', '', $_REQUEST['name']);
$package->type = $_REQUEST['type'];
switch ($package->type) {
	case 'component':
		$package->component = $_REQUEST['pkg_component'];
		break;
	case 'template':
		$package->component = $_REQUEST['pkg_template'];
		break;
	case 'system':
		unset($package->component);
		break;
	case 'meta':
		unset($package->component);
		$package->meta = array(
			'name' => $_REQUEST['meta_name'],
			'author' => $_REQUEST['meta_author'],
			'version' => $_REQUEST['meta_version'],
			'license' => $_REQUEST['meta_license'],
			'short_description' => $_REQUEST['meta_short_description'],
			'description' => $_REQUEST['meta_description'],
			'depend' => array(),
			'recommend' => array(),
			'conflict' => array()
		);
		$conditions = json_decode($_REQUEST['meta_conditions']);
		foreach ($conditions as $cur_condition) {
			switch ($cur_condition->values[0]) {
				case 'depend':
					$package->meta['depend'][$cur_condition->values[1]] = $cur_condition->values[2];
					break;
				case 'recommend':
					$package->meta['recommend'][$cur_condition->values[1]] = $cur_condition->values[2];
					break;
				case 'conflict':
					$package->meta['conflict'][$cur_condition->values[1]] = $cur_condition->values[2];
					break;
			}
		}
		break;
	default:
		$package->type = 'component';
		$package->component = $_REQUEST['pkg_component'];
		pines_notice('Package type must be either component, template, system, or meta.');
		break;
}
$package->filename = $_REQUEST['filename'];

// Attributes
$package->attributes = (array) json_decode($_REQUEST['attributes']);
foreach ($package->attributes as &$cur_attribute) {
	$array = array(
		'name' => $cur_attribute->values[0],
		'value' => $cur_attribute->values[1]
	);
	$cur_attribute = $array;
}
unset($cur_attribute);

if (empty($package->name)) {
	$package->print_form();
	pines_notice('Please specify a name.');
	return;
}
if (!in_array($package->type, array('system', 'meta')) && empty($package->component)) {
	$package->print_form();
	pines_notice('Please specify a component.');
	return;
}
if (!in_array($package->type, array('system', 'meta')) && !in_array($package->component, $pines->all_components)) {
	$package->print_form();
	pines_notice('Selected component was not found.');
	return;
}
$test = $pines->entity_manager->get_entity(array('data' => array('name' => $package->name), 'tags' => array('com_packager', 'package'), 'class' => com_packager_package));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$package->print_form();
	pines_notice('There is already a package with that name. Please choose a different name.');
	return;
}

if ($pines->config->com_packager->global_packages)
	$package->ac->other = 1;

if ($package->save()) {
	pines_notice('Saved package ['.$package->name.']');
} else {
	pines_error('Error saving package. Do you have permission?');
}

redirect(pines_url('com_packager', 'listpackages'));

?>