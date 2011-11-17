<?php
/**
 * com_menueditor class.
 *
 * @package Pines
 * @subpackage com_menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_menueditor main class.
 *
 * @package Pines
 * @subpackage com_menueditor
 */
class com_menueditor extends component {
	/**
	 * Creates and attaches a module which lists entries.
	 * @return module The module.
	 */
	public function list_entries() {
		global $pines;

		$module = new module('com_menueditor', 'entry/list', 'content');

		$module->entries = $pines->entity_manager->get_entities(
				array('class' => com_menueditor_entry),
				array('&',
					'tag' => array('com_menueditor', 'entry')
				)
			);

		if ( empty($module->entries) )
			pines_notice('No entries found.');

		return $module;
	}

	/**
	 * Creates and attaches example modules in various positions.
	 */
	public function print_content() {
		$module = new module('com_menueditor', 'content/short', 'content_top_left');
		$module = new module('com_menueditor', 'content/short', 'content_top_right');
		$module = new module('com_menueditor', 'content/medium', 'pre_content');
		$module = new module('com_menueditor', 'content/title', 'breadcrumbs');
		$module = new module('com_menueditor', 'content/long', 'content');
		$module = new module('com_menueditor', 'content/medium', 'post_content');
		$module = new module('com_menueditor', 'content/short', 'content_bottom_left');
		$module = new module('com_menueditor', 'content/short', 'content_bottom_right');
		$module = new module('com_menueditor', 'content/short', 'left');
		$module = new module('com_menueditor', 'content/short', 'right');
		//$module = new module('com_menueditor', 'content/medium', 'left');
		$module = new module('com_menueditor', 'content/medium', 'right');
		$module = new module('com_menueditor', 'content/short', 'top');
		$module = new module('com_menueditor', 'content/short', 'header');
		$module = new module('com_menueditor', 'content/short', 'header_right');
		$module = new module('com_menueditor', 'content/medium', 'footer');
		$module = new module('com_menueditor', 'content/short', 'bottom');
	}
}

?>