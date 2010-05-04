<?php
/**
 * com_tangoicons class.
 *
 * @package Pines
 * @subpackage com_tangoicons
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_tangoicons main class.
 *
 * A Pines Icon theme using the Tango icon library.
 *
 * @package Pines
 * @subpackage com_tangoicons
 */
class com_tangoicons extends component {
	/**
	 * Whether the Tango CSS has been loaded.
	 * @access private
	 * @var bool $css_loaded
	 */
	private $css_loaded = false;

	/**
	 * Load the icon set.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->css_loaded) {
			$module = new module('com_tangoicons', 'tangoicons', 'head');
			$module->render();
			$this->css_loaded = true;
		}
	}
}

?>