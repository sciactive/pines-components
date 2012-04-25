<?php
/**
 * com_fancybox class.
 *
 * @package Components
 * @subpackage fancybox
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_fancybox main class.
 *
 * A JavaScript image slideshow widget.
 *
 * @package Components
 * @subpackage fancybox
 */
class com_fancybox extends component {
	/**
	 * Whether the fancybox JavaScript has been loaded.
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
			$module = new module('com_fancybox', 'fancybox', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>