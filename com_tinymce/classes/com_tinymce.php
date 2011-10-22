<?php
/**
 * com_tinymce class.
 *
 * @package Pines
 * @subpackage com_tinymce
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_tinymce main class.
 *
 * A standard editor using TinyMCE.
 *
 * @package Pines
 * @subpackage com_tinymce
 */
class com_tinymce extends component implements editor_interface {
	/**
	 * Whether the TinyMCE JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;
	/**
	 * The CSS files to load.
	 * @access private
	 * @var array $css_files
	 */
	private $css_files = array();

	public function add_css($url) {
		$this->css_files[] = clean_filename($url);
	}

	/**
	 * Get the CSS file array.
	 * @return array The CSS file array.
	 */
	public function get_css() {
		return $this->css_files;
	}

	public function load() {
		if (!$this->js_loaded) {
			$module = new module('com_tinymce', 'tinymce', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>