<?php
/**
 * com_soundmanager class.
 *
 * @package Pines
 * @subpackage com_soundmanager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_soundmanager main class.
 *
 * A sound management JavaScript library.
 *
 * @package Pines
 * @subpackage com_soundmanager
 */
class com_soundmanager extends component {
	/**
	 * Whether the soundmanager JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the JavaScript.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_soundmanager', 'soundmanager', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>