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
	 * Whether the menu editor JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded_editor
	 */
	private $js_loaded_editor = false;

	/**
	 * Load the menu editor.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	public function load_editor() {
		if (!$this->js_loaded_editor) {
			$module = new module('com_menueditor', 'editor', 'head');
			$module->render();
			$this->js_loaded_editor = true;
		}
	}

	/**
	 * Verify the entries provided by the user when using the editor.
	 * 
	 * If the entries don't check out in any way this will return false and
	 * provide a message to the user through pines_notice.
	 * 
	 * @param array $entries_array The entries to check.
	 * @param bool $notify When set to false, no notices are generated.
	 * @return bool True when entries are verified clean.
	 */
	public function check_entries($entries_array, $notify = true) {
		$return = true;
		foreach ($entries_array as $cur_entry) {
			if (empty($cur_entry['name'])) {
				if ($notify)
					pines_notice('Please specify a name for all menu entries.');
				$return = false;
			}
			if (!empty($cur_entry['onclick']) && !gatekeeper('com_menueditor/jsentry')) {
				if ($notify)
					pines_notice('You don\'t have permission to add onclick JavaScript to menu entries.');
				$return = false;
			}

			if (isset($cur_entry['top_menu'])) {
				if (empty($cur_entry['top_menu'])) {
					if ($notify)
						pines_notice('Please specify a menu for all menu entries.');
					$return = false;
				}
				if (empty($cur_entry['location'])) {
					if ($notify)
						pines_notice('Please specify a location for all menu entries.');
					$return = false;
				}
			} else {
				if (empty($cur_entry['position'])) {
					if ($notify)
						pines_notice('Please specify a position for all menu entries.');
					$return = false;
				}
			}
		}
		return $return;
	}

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