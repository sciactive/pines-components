<?php
/**
 * com_bootstrap class.
 *
 * @package Components\bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_bootstrap main class.
 *
 * @package Components\bootstrap
 */
class com_bootstrap extends component {
	/**
	 * Whether the Bootstrap scripts have been loaded.
	 * @access private
	 * @var bool $css_loaded
	 */
	private $scripts_loaded = false;

	/**
	 * Load the Bootstrap scripts.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->scripts_loaded) {
			$module = new module('com_bootstrap', 'bootstrap', 'head');
			// Not needed since no other libraries are loaded.
			//$module->render();
			$this->scripts_loaded = true;
		}
	}
}

?>