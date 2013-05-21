<?php
/**
 * com_elastislide's class.
 *
 * @package Components\elastislide
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
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
 * @package Components\elastislide
 */
class com_elastislide extends component {
	/**
	 * Whether the elastislide JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the elastislide.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_elastislide', 'elastislide', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>