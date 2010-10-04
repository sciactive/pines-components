<?php
/**
 * com_plaza class.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_plaza main class.
 *
 * @package Pines
 * @subpackage com_plaza
 */
class com_plaza extends component {
	/**
	 * Creates and attaches a module which lists packages.
	 */
	public function list_packages() {
		global $pines;

		$module = new module('com_plaza', 'package/list', 'content');

		$module->db = $pines->com_package->db;
	}

	/**
	 * Creates and attaches a module which lists repository packages.
	 */
	public function list_repository() {
		global $pines;

		$module = new module('com_plaza', 'package/repository', 'content');

		$module->db = $pines->com_package->db;
		$module->index = $this->get_index();
	}

	/**
	 * Reload the package indices from the repositories.
	 *
	 * @return bool True on success, false on failure.
	 * @todo Remove old indicies after the repository is removed.
	 */
	public function reload_packages() {
		global $pines;
		if (class_exists('HttpRequest')) {
			$fetch = 'pecl';
		} elseif (function_exists('curl_init')) {
			$fetch = 'curl';
		} else {
			$fetch = 'file_get';
		}
		$return = true;
		foreach ($pines->config->com_plaza->repositories as $cur_repository) {
			$cache_file = 'components/com_plaza/includes/cache/indices/'.md5($cur_repository);
			$cur_url = $cur_repository . (strpos($cur_repository, '?') === false ? '?' : '&') . 'option=com_repository&action=getindex';
			switch ($fetch) {
				case 'pecl':
					$hr = new HttpRequest($cur_url, HTTP_METH_GET, array('redirect' => 2));
					try {
						$hr->send();
					} catch (Exception $e) {
						$return = false;
						continue;
					}
					$output = $hr->getResponseBody();
					break;
				case 'curl':
					// Get the index.
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $cur_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$output = curl_exec($ch);
					curl_close($ch);
					break;
				case 'file_get':
					$output = file_get_contents($cur_url);
					break;
			}
			if (empty($output)) {
				$return = false;
				continue;
			}
			$index = (array) json_decode($output);
			if (empty($index)) {
				$return = false;
				continue;
			}
			if (!file_put_contents("{$cache_file}.tmp", $output)) {
				$return = false;
				continue;
			}
			if (!rename("{$cache_file}.tmp", "{$cache_file}.index")) {
				$return = false;
				continue;
			}
		}
		return $return;
	}

	/**
	 * Get an index of packages.
	 *
	 * @param string $repository Only retrieve from this repository.
	 * @param string $publisher Only retrieve from this publisher.
	 * @return array An array of packages.
	 */
	public function get_index($repository = null, $publisher = null) {
		if (isset($repository)) {
			$files = array('components/com_plaza/includes/cache/indices/'.md5($cur_repository).'.index');
		} else {
			$files = glob('components/com_plaza/includes/cache/indices/*.index');
		}
		$index = array();
		foreach ($files as $cur_file) {
			if (!file_exists($cur_file))
				continue;
			$cur_index = (array) json_decode(file_get_contents($cur_file), true);
			if (isset($publisher)) {
				$index = array_merge((array) $cur_index[$publisher], $index);
			} else {
				foreach ($cur_index as $cur_pub_index) {
					$index = array_merge((array) $cur_pub_index, $index);
				}
			}
		}
		return array('packages' => $index);
	}
}

?>