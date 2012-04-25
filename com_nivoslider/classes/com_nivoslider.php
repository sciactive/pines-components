<?php
/**
 * com_nivoslider class.
 *
 * @package Components\nivoslider
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_nivoslider main class.
 *
 * A JavaScript image slider widget.
 *
 * @package Components\nivoslider
 */
class com_nivoslider extends component {
	/**
	 * Whether the nivoslider JavaScript has been loaded.
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
			$module = new module('com_nivoslider', 'nivoslider', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>