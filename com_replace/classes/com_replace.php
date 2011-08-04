<?php
/**
 * com_replace class.
 *
 * @package Pines
 * @subpackage com_replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_replace main class.
 *
 * @package Pines
 * @subpackage com_replace
 */
class com_replace extends component {
	/**
	 * Creates and attaches a module which lists replacements.
	 * @return module The module.
	 */
	public function list_replacements() {
		global $pines;

		$module = new module('com_replace', 'replacement/list', 'content');

		$module->replacements = $pines->entity_manager->get_entities(array('class' => com_replace_replacement), array('&', 'tag' => array('com_replace', 'replacement')));

		if ( empty($module->replacements) )
			pines_notice('There are no replacements.');

		return $module;
	}

	/**
	 * Process search and replace strings.
	 *
	 * @param string &$content The content to search.
	 */
	public function search_replace(&$content) {
		global $pines;

		// Gather enabled replacements.
		$replacements = (array) $pines->entity_manager->get_entities(
				array('class' => com_replace_replacement),
				array('&',
					'tag' => array('com_replace', 'replacement'),
					'data' => array('enabled', true)
				)
			);

		// Process search/replace.
		foreach ($replacements as $cur_replacement) {
			if (!$cur_replacement->ready())
				continue;
			foreach ($cur_replacement->strings as $cur_string) {
				$content = str_replace($cur_string['search'], $cur_string['replace'], $content);
			}
		}
	}
}

?>