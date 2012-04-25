<?php
/**
 * com_inuitcss class.
 *
 * @package Components\inuitcss
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_inuitcss main class.
 *
 * @package Components\inuitcss
 */
class com_inuitcss extends component {
	/**
	 * Whether the Inuit CSS has been loaded.
	 * @access private
	 * @var bool $css_loaded
	 */
	private $css_loaded = false;

	/**
	 * Load the Inuit CSS.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->css_loaded) {
			$module = new module('com_inuitcss', 'inuit', 'head');
			// Not needed since no other libraries are loaded.
			//$module->render();
			$this->css_loaded = true;
		}
	}
}

?>