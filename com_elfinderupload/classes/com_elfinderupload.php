<?php
/**
 * com_elfinderupload class.
 *
 * @package Pines
 * @subpackage com_elfinderupload
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_elfinderupload main class.
 *
 * A standard Pines file upload widget built with elFinder.
 *
 * @package Pines
 * @subpackage com_elfinderupload
 */
class com_elfinderupload extends component implements uploader_interface {
	/**
	 * Whether the JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load elFinder and convert inputs.
	 */
	public function load() {
		if (!$this->js_loaded) {
			global $pines;
			$pines->com_elfinder->load();
			$module = new module('com_elfinderupload', 'elfinder', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}

	public function check($url) {
		if (empty($url))
			return false;
		global $pines;
		$root_url = $pines->config->com_elfinder->root_url;
		if (strpos($url, '..') !== false)
			return false;
		return (substr($url, 0, strlen($root_url)) == $root_url);
	}

	public function real($url) {
		if (empty($url))
			return '';
		global $pines;
		$root = $pines->config->com_elfinder->root;
		$root_url = $pines->config->com_elfinder->root_url;
		return $root . join('/', array_map('urldecode', explode('/', substr($url, strlen($root_url)))));
	}

	public function temp($temp_string) {
		list($request_id, $temp_name) = explode('/', $temp_string, 2);
		if (!isset($_SESSION['elfinder_request_id'][$request_id]))
			return false;
		$filename = sys_get_temp_dir().'/'.$_SESSION['elfinder_request_id'][$request_id].'/'.clean_filename($temp_name);
		if (!file_exists($filename))
			return false;
		return $filename;
	}

	public function url($real, $full = false) {
		if (empty($real))
			return '';
		global $pines;
		$root = $pines->config->com_elfinder->root;
		if ($full)
			$root_url = $pines->config->com_elfinder->full_root_url;
		else
			$root_url = $pines->config->com_elfinder->root_url;
		return $root_url . join('/', array_map('urlencode', explode('/', substr($real, strlen($root)))));
	}
}

?>