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
	public function list_widgets() {
		global $pines;

		$module = new module('com_example', 'widget/list', 'content');

		$module->widgets = $pines->entity_manager->get_entities(array('class' => com_example_widget), array('&', 'tag' => array('com_example', 'widget')));

		if ( empty($module->widgets) )
			pines_notice('There are no widgets.');
	}

	/**
	 * Creates and attaches example modules in various positions.
	 */
	public function print_content() {
		$module = new module('com_example', 'content/short', 'content_top_left');
		$module = new module('com_example', 'content/short', 'content_top_right');
		$module = new module('com_example', 'content/medium', 'pre_content');
		$module = new module('com_example', 'content/long', 'content');
		$module = new module('com_example', 'content/medium', 'post_content');
		$module = new module('com_example', 'content/short', 'content_bottom_left');
		$module = new module('com_example', 'content/short', 'content_bottom_right');
		$module = new module('com_example', 'content/short', 'left');
		$module = new module('com_example', 'content/short', 'right');
		//$module = new module('com_example', 'content/medium', 'left');
		$module = new module('com_example', 'content/medium', 'right');
		$module = new module('com_example', 'content/short', 'top');
		$module = new module('com_example', 'content/short', 'header');
		$module = new module('com_example', 'content/short', 'header_right');
		$module = new module('com_example', 'content/medium', 'footer');
		$module = new module('com_example', 'content/short', 'bottom');
	}
}

?>