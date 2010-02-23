<?php
/**
 * com_jquery class.
 *
 * @package Pines
 * @subpackage com_jquery
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_jquery main class.
 *
 * jQuery loader.
 *
 * @package Pines
 * @subpackage com_tinymce
 */
class com_jquery extends component {
	/**
	 * Whether the jQuery JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load jQuery and jQuery UI.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_jquery', 'jquery', 'head');
			$module = new module('com_jquery', 'jquery-ui', 'head');
			$this->js_loaded = true;
		}
	}
}

?>