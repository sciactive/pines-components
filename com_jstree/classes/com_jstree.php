<?php
/**
 * com_jstree class.
 *
 * @package Pines
 * @subpackage com_jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_jstree main class.
 *
 * A JavaScript tree widget.
 *
 * @package Pines
 * @subpackage com_jstree
 */
class com_jstree extends component {
	/**
	 * Whether the jstree JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the tree.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_jstree', 'jstree', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>