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
// Also check for a _MEDIA dir.
$has_media = false;
foreach ($files as $cur_file) {
	$has_media = $has_media || $cur_file['path'] == '_MEDIA/';
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
		if (strpos($cur_file['path'], $component) !== 0 && strpos($cur_file['path'], '_MEDIA/') !== 0) {
			pines_notice('Component/template package contains outside files.');
			redirect(pines_url('com_repository', 'listpackages'));
			return;
		}
	}
}

if ($package->ext['screens'] && count($package->ext['screens']) > 10) {
	pines_notice('Maximum 10 screen shots allowed.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

// Move package into repository.
$dir = clean_filename($pines->config->com_repository->repository_path.$_SESSION['user']->guid.'/'.$package->ext['package'].'/'.$package->ext['version'].'/');
$filename = $dir.clean_filename("{$package->ext['package']}-{$package->ext['version']}.slm");
$sig_filename = $dir.clean_filename("{$package->ext['package']}-{$package->ext['version']}.sig");
if (file_exists($sig_filename) && !unlink($sig_filename)) {
	pines_error('Old signature file couldn\'t be removed.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

if (!file_exists($dir) && !mkdir($dir, 0700, true)) {
	pines_error('Error creating user directory.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

if ($has_media) {
	// Extract the media directory.
	$package->working_directory = $dir;
	$package->extract('_MEDIA/', true, '/^_MEDIA\/.*\//');
	$media = glob("{$dir}/_MEDIA/*");
	foreach ($media as $cur_media) {
		if (!chmod($cur_media, 0600)) {
			unlink($cur_media);
			continue;
		}
		if (filesize($cur_media) > 307200) {
			// Max size 300KB.
			pines_notice('Max media filesize is 300KB. Please remove media bigger than 300KB.');
			unlink($cur_media);
			redirect(pines_url('com_repository', 'listpackages'));
			return;
		}
		$image = new Imagick;
		if (!$image->readImage($cur_media)) {
			pines_notice('Couldn\'t read media "'.basename($cur_media).'". Please only upload images only.');
			unlink($cur_media);
			redirect(pines_url('com_repository', 'listpackages'));
			return;
		}
	}
	if (count($media) >= 11) {
		pines_notice('Maximum of 11 media files allowed. 10 screenshots and 1 icon.');
		foreach ($media as $cur_media) {
			unlink($cur_media);
		}
		redirect(pines_url('com_repository', 'listpackages'));
		return;
	}
}

if (!move_uploaded_file($_FILES['package']['tmp_name'], $filename)) {
	pines_error('Error moving package into repository.');
	redirect(pines_url('com_repository', 'listpackages'));
	return;
}

pines_notice('Saved package ['.$package->ext['package'].']. Now you should refresh your index to see it.');

redirect(pines_url('com_repository', 'listpackages'));

?>