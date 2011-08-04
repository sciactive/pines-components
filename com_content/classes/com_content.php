<?php
/**
 * com_content class.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_content main class.
 *
 * Manage content pages.
 *
 * @package Pines
 * @subpackage com_content
 */
class com_content extends component {
	/**
	 * Creates and attaches a module which lists categories.
	 * @return module The module.
	 */
	public function list_categories() {
		global $pines;

		$module = new module('com_content', 'category/list', 'content');

		$module->categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'tag' => array('com_content', 'category')));

		if ( empty($module->categories) )
			pines_notice('No categories found.');

		return $module;
	}

	/**
	 * Creates and attaches a module which lists pages.
	 *
	 * @param com_content_category $category The category to list pages from. If null, all pages will be listed.
	 * @return module The module.
	 */
	public function list_pages($category = null) {
		global $pines;

		$module = new module('com_content', 'page/list', 'content');

		if (isset($category)) {
			$module->pages = $category->pages;
			$module->category = $category;
		} else {
			$module->pages = $pines->entity_manager->get_entities(array('class' => com_content_page), array('&', 'tag' => array('com_content', 'page')));
		}

		if ( empty($module->pages) )
			pines_notice('No pages found.');

		return $module;
	}
}

?>