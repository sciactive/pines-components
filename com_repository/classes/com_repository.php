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
	 * Generate repository indices.
	 *
	 * @param user $user Only generate indices for this user.
	 */
	public function make_indices($user = null) {
		global $pines;
		if (isset($user)) {
			$guids = array($user->guid);
		} else {
			$guids = array_filter(array_map('basename', glob($pines->config->com_repository->repository_path.'*')), 'is_numeric');
		}
		$slim = new slim;
		if (!isset($user))
			$main_index = array();
		foreach ($guids as $cur_guid) {
			// Build an index for the current directory.
			$tmp_user = user::factory((int) $cur_guid);
			if (!isset($tmp_user->guid))
				continue;
			$dir = $pines->config->com_repository->repository_path.$cur_guid.'/';
			$index = array();
			$packages = glob($dir.'*.slm');
			foreach ($packages as $cur_package) {
				if (!$slim->read($cur_package))
					continue;
				if (isset($index[$slim->ext['package']]) && version_compare($slim->ext['version'], $index[$slim->ext['package']]['version']) == -1)
					continue;
				$index[$slim->ext['package']] = array(
					'publisher' => $tmp_user->username,
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
					'conflict' => $slim->ext['conflict']
				);
			}
			if (!file_put_contents($dir.'index.tmp', json_encode($index)))
				continue;
			if (rename($dir.'index.tmp', $dir.'index.json') && !isset($user))
				$main_index[$tmp_user->username] = $index;
			unset($tmp_user);
		}
		if (!isset($user)) {
			$dir = $pines->config->com_repository->repository_path;
			if (!file_put_contents($dir.'index.tmp', json_encode($main_index)))
				return;
			rename($dir.'index.tmp', $dir.'index.json');
		}
	}

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
}

?>