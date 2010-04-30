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
$package->name = $_REQUEST['name'];
$package->type = $_REQUEST['type'];
switch ($package->type) {
	case 'component':
		$package->component = $_REQUEST['pkg_component'];
		break;
	case 'template':
		$package->component = $_REQUEST['pkg_template'];
		break;
	case 'meta':
		unset($package->component);
		$package->meta = array('title' => $_REQUEST['meta_title']);
		break;
	default:
		$package->type = 'component';
		$package->component = $_REQUEST['pkg_component'];
		pines_notice('Package type must be either component or template.');
		break;
}

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
if ($package->type != 'meta' && empty($package->component)) {
	$package->print_form();
	pines_notice('Please specify a component.');
	return;
}
if ($package->type != 'meta' && !in_array($package->component, $pines->all_components)) {
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