<?php
/**
 * Save a package to the repository.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_repository/newpackage') )
	punt_user(null, pines_url('com_repository', 'listpackages'));


if ($_FILES['package']['error']) {
	pines_error('Error uploading package.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

$package = new slim;
if (!$package->read($_FILES['package']['tmp_name'])) {
	pines_error('Error reading package.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

// Check that the package is valid.
if (
		empty($package->ext['package']) || preg_match('/[^a-z0-9_-]/', $package->ext['package']) ||
		!in_array($package->ext['type'], array('component', 'template', 'system', 'meta')) ||
		empty($package->ext['name']) ||
		empty($package->ext['author']) ||
		empty($package->ext['version'])
	) {
	pines_notice('Package is not valid.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

// Check that the files aren't dangerous.
$files = $package->get_current_files();
foreach ($files as $cur_file) {
	if (!is_clean_filename($cur_file['path'])) {
		pines_notice('Package contains dangerous files.');
		redirect(pines_url('com_repository', 'listpackages'));
		return;
	}
}
if (in_array($package->ext['type'], array('component', 'template'))) {
	$component = ($package->ext['type'] == 'component' ? preg_replace('/^(com_[a-z0-9]+\/)?.*$/', '$1', $files[0]['path']) : preg_replace('/^(tpl_[a-z0-9]+\/)?.*$/', '$1', $files[0]['path']));
	if (empty($component)) {
		pines_notice('Component/template package contains outside files.');
		redirect(pines_url('com_repository', 'listpackages'));
		return;
	}
	if ($component != $package->ext['package'].'/') {
		pines_notice('Component/template package is not named correctly.');
		redirect(pines_url('com_repository', 'listpackages'));
		return;
	}
	foreach ($files as $cur_file) {
		if (strpos($cur_file['path'], $component) !== 0) {
			pines_notice('Component/template package contains outside files.');
			redirect(pines_url('com_repository', 'listpackages'));
			return;
		}
	}
}

// Move package into repository.
$dir = $pines->config->com_repository->repository_path.$_SESSION['user']->guid.'/';
$filename = $dir.clean_filename("{$package->ext['package']}-{$package->ext['version']}.slm");

if (!file_exists($dir) && !mkdir($dir)) {
	pines_error('Error creating user directory.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

if (!move_uploaded_file($_FILES['package']['tmp_name'], $filename)) {
	pines_error('Error moving package into repository.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

pines_notice('Saved package ['.$package->ext['package'].']. Now you should refresh your index to see it.');

redirect(pines_url('com_repository', 'listpackages'));

?>