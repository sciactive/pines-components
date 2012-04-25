<?php
/**
 * com_elfinder class.
 *
 * @package Components\elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_elfinder main class.
 *
 * A file manager built with elFinder.
 *
 * Thanks to Studio 42, the authors of elFinder, for their fantastic work.
 *
 * @package Components\elfinder
 */
class com_elfinder extends component {
	/**
	 * Whether the elFinder JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the elFinder JavaScript and CSS files.
	 */
	public function load() {
		if (!$this->js_loaded) {
			$module = new module('com_elfinder', 'finder_head', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>