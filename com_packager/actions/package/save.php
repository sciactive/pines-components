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
		punt_user(null, pines_url('com_packager', 'package/list'));
	$package = com_packager_package::factory((int) $_REQUEST['id']);
	if (!isset($package->guid)) {
		pines_error('Requested package id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_packager/newpackage') )
		punt_user(null, pines_url('com_packager', 'package/list'));
	$package = com_packager_package::factory();
}

// General
$package->type = $_REQUEST['type'];
switch ($package->type) {
	case 'component':
		$package->name = preg_replace('/[^a-z0-9_-]/', '', $_REQUEST['pkg_component']);
		$package->component = $_REQUEST['pkg_component'];
		break;
	case 'template':
		$package->name = preg_replace('/[^a-z0-9_-]/', '', $_REQUEST['pkg_template']);
		$package->component = $_REQUEST['pkg_template'];
		break;
	case 'system':
		$package->name = preg_replace('/[^a-z0-9_-]/', '', $_REQUEST['system_package_name']);
		unset($package->component);
		break;
	case 'meta':
		$package->name = preg_replace('/[^a-z0-9_-]/', '', $_REQUEST['meta_package_name']);
		unset($package->component);
		$package->meta = array(
			'name' => $_REQUEST['meta_name'],
			'author' => $_REQUEST['meta_author'],
			'version' => $_REQUEST['meta_version'],
			'license' => $_REQUEST['meta_license'],
			'website' => $_REQUEST['meta_website'],
			'short_description' => $_REQUEST['meta_short_description'],
			'description' => $_REQUEST['meta_description'],
			'depend' => array(),
			'recommend' => array(),
			'conflict' => array()
		);
		$conditions = (array) json_decode($_REQUEST['meta_conditions']);
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

// Files
$package->additional_files = explode(',', $_REQUEST['additional_files']);
foreach ($package->additional_files as $key => &$cur_file) {
	$cur_file = clean_filename($cur_file);
	if (empty($cur_file))
		unset($package->additional_files[$key]);
}
unset($cur_file);
$package->exclude_files = explode(',', $_REQUEST['exclude_files']);
foreach ($package->exclude_files as $key => &$cur_file) {
	$cur_file = clean_filename($cur_file);
	if (empty($cur_file))
		unset($package->exclude_files[$key]);
}
unset($cur_file);

// Images
$package->screenshots = (array) json_decode($_REQUEST['screenshots'], true);
foreach ($package->screenshots as $key => &$cur_screen) {
	if ($cur_screen['alt'] == 'Click to edit description...')
		$cur_screen['alt'] = '';
	if (!$pines->uploader->check($cur_screen['file']))
		unset($package->screenshots[$key]);
}
unset($cur_screen);
$package->screenshots = array_values($package->screenshots);
$package->icon = $_REQUEST['icon'];
if (!$pines->uploader->check($package->icon))
	$package->icon = null;

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
$test = $pines->entity_manager->get_entity(array('class' => com_packager_package, 'skip_ac' => true), array('&', 'tag' => array('com_packager', 'package'), 'data' => array('name', $package->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$package->print_form();
	pines_notice('There is already a package with that name. Please choose a different name.');
	return;
}
foreach ($package->screenshots as $cur_screen) {
	// Check the size of the images.
	$filesize = (float) filesize($pines->uploader->real($cur_screen['file'])) / 1024;
	if ($filesize > 300) {
		$package->print_form();
		pines_notice(basename($cur_screen['file']).' is '.number_format($filesize).'KB. The maximum screenshot size is 300KB.');
		return;
	}
}

if ($pines->config->com_packager->global_packages)
	$package->ac->other = 1;

if ($package->save()) {
	pines_notice('Saved package ['.$package->name.']');
} else {
	pines_error('Error saving package. Do you have permission?');
}

pines_redirect(pines_url('com_packager', 'package/list'));

?>