<?php
/**
 * com_ckeditor class.
 *
 * @package Components\ckeditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_ckeditor main class.
 *
 * A standard editor using CKEditor.
 *
 * @package Components\ckeditor
 */
class com_ckeditor extends component implements editor_interface {
	/**
	 * Whether the CKEditor JavaScript has been loaded.
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
			$module = new module('com_ckeditor', 'ckeditor', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>