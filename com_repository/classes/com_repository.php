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

		$module->packages = $this->get_index($user);

		if ( empty($module->packages) )
			pines_notice('There are no packages.');
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
			$guids = array_filter(glob($pines->config->com_repository->repository_path), 'is_numeric');
		}
		var_dump($guids);
	}

	/**
	 * Get the index of packages.
	 *
	 * @param user $user Only get this user's index.
	 * @return array The index.
	 */
	public function get_index($user = null) {
		global $pines;
		$file = $pines->config->com_repository->repository_path;
		if (isset($user))
			$file .= $user->guid . '/';
		$file .= 'index.json';
		if (!file_exists($file))
			return array();
		return (array) json_decode(file_get_contents($file));
	}
}

?>