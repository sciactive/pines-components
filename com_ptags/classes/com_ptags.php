<?php
/**
 * com_ptags class.
 *
 * @package Components\ptags
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_ptags main class.
 *
 * A JavaScript content tag editor.
 *
 * @package Components\ptags
 */
class com_ptags extends component {
	/**
	 * Whether the ptags JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the tag editor.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_ptags', 'ptags', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>