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
	 * Add menu entries from the menu editor to the menu system.
	 * @param array $entries_array An array of entries from the menu editor.
	 * @param array $override_values An optional array of values that will override values from the entries. Should be in key => value format.
	 * @return array The menu arrays that were added to $pines->menu->menu_arrays.
	 */
	public function add_entries($entries_array, $override_values = array()) {
		global $pines;
		$return = array();
		foreach ($entries_array as $cur_entry) {
			$cur_entry = array_merge($cur_entry, $override_values);
			if (!$cur_entry['enabled'])
				continue;
			if (isset($cur_entry['top_menu'])) {
				$array = array(
					'path' => $cur_entry['location'].'/'.$cur_entry['name'],
					'text' => $cur_entry['text']
				);
			} else {
				$array = array(
					'path' => $cur_entry['name'],
					'text' => $cur_entry['text'],
					'position' => $cur_entry['position']
				);
			}
			if ($cur_entry['sort'])
				$array['sort'] = true;
			if (!empty($cur_entry['link']))
				$array['href'] = $cur_entry['link'];
			if (!empty($cur_entry['onclick']))
				$array['onclick'] = $cur_entry['onclick'];
			$depend = $cur_entry['conditions'];
			if ($cur_entry['children'])
				$depend['children'] = true;
			$array['depend'] = $depend;
			$pines->menu->menu_arrays[] = $array;
			$return[] = $array;
		}
		return $return;
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
}

?>