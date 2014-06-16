<?php
/**
 * jqueryuitouch's component class
 *
 * @package Components\jqueryuitouch
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_jqueryuitouch main class.
 *
 * A JavaScript jQuery UI Library extension.
 *
 * @package Components\jqueryuitouch
 */
class com_jqueryuitouch extends component {
	/**
	 * Whether the jqueryuitouch JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the jqueryuitouch JS.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		global $pines;
		if (!$this->js_loaded) {
			if ($pines->config->compress_cssjs) {
				$file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
				$js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
				$js[] =  $file_root.'components/com_jqueryuitouch/includes/'.($pines->config->debug_mode ? 'jquery.ui.touch-punch.js' : 'jquery.ui.touch-punch.min.js');
				$pines->config->loadcompressedjs = $js;
			} else {
				$module = new module('com_jqueryuitouch', 'jqueryuitouch', 'head');
				$module->render();
			}
			$this->js_loaded = true;
		}
	}
}
?>