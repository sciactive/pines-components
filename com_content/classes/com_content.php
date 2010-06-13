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
 * Manage content articles.
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
	 * Creates and attaches a module which lists articles.
	 */
	public function list_articles() {
		global $pines;

		$module = new module('com_content', 'article/list', 'content');

		$module->articles = $pines->entity_manager->get_entities(array('class' => com_content_article), array('&', 'tag' => array('com_content', 'article')));

		if ( empty($module->articles) )
			pines_notice('There are no articles.');
	}
}

?>