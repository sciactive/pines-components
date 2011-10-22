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
/* @var $pines pines */
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
				if ($cur_string['macros']) {
					$search = array();
					$replace = array();
					if (strpos($cur_string['replace'], '#username#') !== false) {
						$search[] = '#username#';
						$replace[] = htmlspecialchars($_SESSION['user']->username);
					}
					if (strpos($cur_string['replace'], '#name#') !== false) {
						$search[] = '#name#';
						$replace[] = htmlspecialchars($_SESSION['user']->name);
					}
					if (strpos($cur_string['replace'], '#first_name#') !== false) {
						$search[] = '#first_name#';
						$replace[] = htmlspecialchars($_SESSION['user']->first_name);
					}
					if (strpos($cur_string['replace'], '#last_name#') !== false) {
						$search[] = '#last_name#';
						$replace[] = htmlspecialchars($_SESSION['user']->last_name);
					}
					if (strpos($cur_string['replace'], '#email#') !== false) {
						$search[] = '#email#';
						$replace[] = htmlspecialchars($_SESSION['user']->email);
					}
					if (strpos($cur_string['replace'], '#date_short#') !== false) {
						$search[] = '#date_short#';
						$replace[] = htmlspecialchars(format_date(time(), 'date_short'));
					}
					if (strpos($cur_string['replace'], '#date_med#') !== false) {
						$search[] = '#date_med#';
						$replace[] = htmlspecialchars(format_date(time(), 'date_med'));
					}
					if (strpos($cur_string['replace'], '#date_long#') !== false) {
						$search[] = '#date_long#';
						$replace[] = htmlspecialchars(format_date(time(), 'date_long'));
					}
					if (strpos($cur_string['replace'], '#time_short#') !== false) {
						$search[] = '#time_short#';
						$replace[] = htmlspecialchars(format_date(time(), 'time_short'));
					}
					if (strpos($cur_string['replace'], '#time_med#') !== false) {
						$search[] = '#time_med#';
						$replace[] = htmlspecialchars(format_date(time(), 'time_med'));
					}
					if (strpos($cur_string['replace'], '#time_long#') !== false) {
						$search[] = '#time_long#';
						$replace[] = htmlspecialchars(format_date(time(), 'time_long'));
					}
					if (strpos($cur_string['replace'], '#system_name#') !== false) {
						$search[] = '#system_name#';
						$replace[] = htmlspecialchars($pines->config->system_name);
					}
					if (strpos($cur_string['replace'], '#page_title#') !== false) {
						$search[] = '#page_title#';
						$replace[] = htmlspecialchars($pines->config->page_title);
					}
					if (strpos($cur_string['replace'], '#full_page_title#') !== false) {
						$search[] = '#full_page_title#';
						$replace[] = htmlspecialchars($pines->page->get_title());
					}
					$cur_string['replace'] = str_replace($search, $replace, $cur_string['replace']);
				}
				$content = str_replace($cur_string['search'], $cur_string['replace'], $content);
			}
		}
	}
}

?>