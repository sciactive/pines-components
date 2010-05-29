<?php
/**
 * com_example class.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_example main class.
 *
 * @package Pines
 * @subpackage com_example
 */
class com_example extends component {
	/**
	 * Creates and attaches a module which lists widgets.
	 */
	function list_widgets() {
		global $pines;

		$module = new module('com_example', 'list_widgets', 'content');

		$module->widgets = $pines->entity_manager->get_entities(array('class' => com_example_widget), array('&', 'tag' => array('com_example', 'widget')));

		if ( empty($module->widgets) ) {
			//$module->detach();
			pines_notice('There are no widgets.');
		}
	}

	/**
	 * Creates and attaches example modules in various positions.
	 */
	function print_content() {
		$module = new module('com_example', 'content_short', 'content_top_left');
		$module = new module('com_example', 'content_short', 'content_top_right');
		$module = new module('com_example', 'content_medium', 'pre_content');
		$module = new module('com_example', 'content_long', 'content');
		$module = new module('com_example', 'content_medium', 'post_content');
		$module = new module('com_example', 'content_short', 'content_bottom_left');
		$module = new module('com_example', 'content_short', 'content_bottom_right');
		$module = new module('com_example', 'content_short', 'left');
		$module = new module('com_example', 'content_short', 'right');
		//$module = new module('com_example', 'content_medium', 'left');
		$module = new module('com_example', 'content_medium', 'right');
		$module = new module('com_example', 'content_short', 'top');
		$module = new module('com_example', 'content_short', 'header');
		$module = new module('com_example', 'content_short', 'header_right');
		$module = new module('com_example', 'content_medium', 'footer');
		$module = new module('com_example', 'content_short', 'bottom');
	}
}

?>