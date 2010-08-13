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
	 */
	public function list_categories() {
		global $pines;

		$module = new module('com_content', 'category/list', 'content');

		$module->categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'tag' => array('com_content', 'category')));

		if ( empty($module->categories) )
			pines_notice('No categories found.');
	}

	/**
	 * Creates and attaches a module which lists pages.
	 */
	public function list_pages() {
		global $pines;

		$module = new module('com_content', 'page/list', 'content');

		$module->pages = $pines->entity_manager->get_entities(array('class' => com_content_page), array('&', 'tag' => array('com_content', 'page')));

		if ( empty($module->pages) )
			pines_notice('There are no pages.');
	}
}

?>