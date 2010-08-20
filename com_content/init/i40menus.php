<?php
/**
 * Show page and category menus.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_content->show_page_menus) {
	$pages = (array) $pines->entity_manager->get_entities(
			array('class' => com_content_page),
			array('&',
				'data' => array('show_menu', true),
				'tag' => array('com_content', 'page')
			)
		);

	if (!$pages)
		return;

	foreach ($pages as $cur_page) {
		// Show the page in the menu.
		if (strpos($cur_page->menu_position, '/') === false) {
			// It's a new top level menu.
			$pines->menu->menu_arrays[] = array(
				'path' => "com_content_page_{$cur_page->guid}",
				'text' => $cur_page->name,
				'position' => $cur_page->menu_position
			);
		} else {
			// It's part of another menu.
			$pines->menu->menu_arrays[] = array(
				'path' => $cur_page->menu_position,
				'text' => $cur_page->name,
				'href' => array('com_content', 'page', array('a' => $cur_page->alias))
			);
		}
	}

	unset($pages, $cur_page);
}

if ($pines->config->com_content->show_cat_menus) {
	$categories = (array) $pines->entity_manager->get_entities(
			array('class' => com_content_category),
			array('&',
				'data' => array('show_menu', true),
				'tag' => array('com_content', 'category')
			)
		);

	if (!$categories)
		return;

	/**
	 * Add a category's children to the menu.
	 * @param com_content_category $category The category.
	 * @param string $path The menu path.
	 */
	function com_content__category_menu($category, $path) {
		global $pines;
		if (!$category->children)
			return;
		foreach ($category->children as $cur_category) {
			$pines->menu->menu_arrays[] = array(
				'path' => "{$path}/cat_{$cur_category->guid}",
				'text' => $cur_category->name,
				'href' => array('com_content', 'category/browse', array('id' => $cur_category->guid))
			);
			if ($cur_category->children)
				com_content__category_menu($cur_category, "{$path}/cat_{$cur_category->guid}");
		}
	}

	foreach ($categories as $cur_category) {
		if (strpos($cur_category->menu_position, '/') === false) {
			// It's a new top level menu.
			$pines->menu->menu_arrays[] = array(
				'path' => "com_content_cat_{$cur_category->guid}",
				'text' => $cur_category->name,
				'position' => $cur_category->menu_position
			);
			com_content__category_menu($cur_category, "com_content_cat_{$cur_category->guid}");
		} else {
			// It's part of another menu.
			$pines->menu->menu_arrays[] = array(
				'path' => $cur_category->menu_position,
				'text' => $cur_category->name,
				'href' => array('com_content', 'category/browse', array('id' => $cur_category->guid))
			);
			com_content__category_menu($cur_category, $cur_category->menu_position);
		}
	}

	unset($categories, $cur_category, $module);
}

?>