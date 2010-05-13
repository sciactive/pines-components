<?php
/**
 * com_oxygenicons class.
 *
 * @package Pines
 * @subpackage com_oxygenicons
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_oxygenicons main class.
 *
 * A Pines Icon theme using the Oxygen icon library.
 *
 * @package Pines
 * @subpackage com_oxygenicons
 */
class com_oxygenicons extends component implements icons_interface {
	/**
	 * Whether the Oxygen CSS has been loaded.
	 * @access private
	 * @var bool $css_loaded
	 */
	private $css_loaded = false;

	public function load() {
		if (!$this->css_loaded) {
			$module = new module('com_oxygenicons', 'oxygenicons', 'head');
			$module->render();
			$this->css_loaded = true;
		}
	}
}

?>