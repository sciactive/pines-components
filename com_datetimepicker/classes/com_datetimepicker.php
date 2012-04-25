<?php
/**
 * com_datetimepicker class.
 *
 * @package Components\datetimepicker
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_datetimepicker main class.
 *
 * A JavaScript time picker widget addon.
 *
 * @package Components\datetimepicker
 */
class com_datetimepicker extends component {
	/**
	 * Whether the time picker JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the time picker.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_datetimepicker', 'datetimepicker', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
}

?>