<?php
/**
 * com_repository class.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_repository main class.
 *
 * @package Pines
 * @subpackage com_repository
 */
class com_repository extends component {
	/**
	 * Get the index of packages.
	 *
	 * @param user $user Only get this user's index.
	 * @param bool $decode Decode the JSON before returning it.
	 * @return array|string The index.
	 */
	public function get_index($user = null, $decode = true) {
		global $pines;
		$file = $pines->config->com_repository->repository_path;
		if (isset($user))
			$file .= $user->guid . '/';
		$file .= 'index.json';
		if (!file_exists($file))
			return ($decode ? array() : '[]');
		if ($decode) {
			return (array) json_decode(file_get_contents($file), true);
		} else {
			return file_get_contents($file);
		}
	}

	/**
	 * Creates and attaches a module which lists packages.
	 *
	 * @param user $user Only list packages for this user.
	 */
	public function list_packages($user = null) {
		$module = new module('com_repository', 'list_packages', 'content');

		$module->index = $this->get_index($user);
		$module->user = $user;

		if ( empty($module->index) )
			pines_notice('No indexed packages found.');
	}

	/**
	 * Generate repository index for a publisher.
	 *
	 * @param user $user Generate the index for this user.
	 */
	public function make_index($user) {
		global $pines;
		$guids = array($user->guid);
		$slim = new slim;
		if (!isset($user->guid))
			return;
		// Build an index for the directory.
		$dir = $pines->config->com_repository->repository_path.$user->guid.'/';
		$index = array();
		$packages = glob($dir.'*.slm');
		foreach ($packages as $cur_package) {
			if (!$slim->read($cur_package))
				continue;
			if (isset($index[$slim->ext['package']]) && version_compare($slim->ext['version'], $index[$slim->ext['package']]['version']) == -1)
				continue;
			$index[$slim->ext['package']] = array(
				'publisher' => $user->username,
				'package' => $slim->ext['package'],
				'type' => $slim->ext['type'],
				'name' => $slim->ext['name'],
				'author' => $slim->ext['author'],
				'version' => $slim->ext['version'],
				'license' => $slim->ext['license'],
				'website' => $slim->ext['website'],
				'services' => $slim->ext['services'],
				'short_description' => $slim->ext['short_description'],
				'description' => $slim->ext['description'],
				'depend' => $slim->ext['depend'],
				'recommend' => $slim->ext['recommend'],
				'conflict' => $slim->ext['conflict'],
				'md5' => md5_file($cur_package)
			);
		}
		if (!file_put_contents($dir.'index.tmp', json_encode($index)))
			continue;
		rename($dir.'index.tmp', $dir.'index.json');
	}

	/**
	 * Compile user indices into a main repository index.
	 */
	public function make_index_main() {
		global $pines;
		$dir = $pines->config->com_repository->repository_path;
		$index_files = glob($dir.'*/index.json');
		$index = array();
		foreach ($index_files as $cur_index_file) {
			$cur_index = json_decode(file_get_contents($cur_index_file), true);
			if ((array) $cur_index !== $cur_index)
				continue;
			$index = array_merge($index, $cur_index);
		}
		if (!file_put_contents($dir.'index.tmp', json_encode($index)))
			return;
		rename($dir.'index.tmp', $dir.'index.json');
	}
}

?>