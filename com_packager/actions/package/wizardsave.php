<?php
/**
 * Save packages from the wizard.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_packager/newpackage') )
	punt_user(null, pines_url('com_packager', 'package/wizard'));

$errors = array();
if (!$_REQUEST['packages']) {
	pines_notice('No components selected.');
	pines_redirect(pines_url('com_packager', 'package/list'));
}
foreach ((array) $_REQUEST['packages'] as $cur_component) {
	if (!in_array($cur_component, $pines->components))
		continue;
	if (!is_null($pines->entity_manager->get_entity(
			array('class' => com_packager_package),
			array('&',
				'tag' => array('com_packager', 'package'),
				'strict' => array('name', $cur_component)
			)
		)))
		continue;
	$package = com_packager_package::factory();
	$package->type = substr($cur_component, 0, 4) == 'tpl_' ? 'template' : 'component';
	$package->name = preg_replace('/[^a-z0-9_-]/', '', $cur_component);
	$package->component = $cur_component;
	$package->filename = '';
	$package->additional_files = array();
	$package->exclude_files = array();
	$package->screenshots = array();
	$package->icon = null;
	if ($pines->config->com_packager->global_packages)
		$package->ac->other = 1;
	if (!$package->save())
		$errors[] = $cur_component;
}

if ($errors)
	pines_error('Couldn\'t create the following packages: '.implode(', ', $errors));
else
	pines_notice('Created packages successfully.');

pines_redirect(pines_url('com_packager', 'package/list'));

?>