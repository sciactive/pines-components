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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_content->show_page_menus) {
	$pages = (array) $pines->entity_manager->get_entities(
			array('class' => com_content_page),
			array('&',
				'tag' => array('com_content', 'page'),
				'data' => array(
					array('enabled', true),
					array('show_menu', true)
				)
			)
		);

	if (!$pages)
		return;

	foreach ($pages as $cur_page) {
		if (!$cur_page->ready())
			continue;
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
				'tag' => array('com_content', 'category'),
				'data' => array(
					array('enabled', true),
					array('show_menu', true)
				)
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
			if (!isset($cur_category) || !$cur_category->ready())
				continue;
			$cat_menu = array(
				'path' => "{$path}/cat_{$cur_category->guid}",
				'text' => $cur_category->name
			);
			if ($cur_category->get_option('link_menu'))
				$cat_menu['href'] = array('com_content', 'category', array('a' => $cur_category->alias));
			$pines->menu->menu_arrays[] = $cat_menu;

			if ($cur_category->get_option('show_pages_in_menu')) {
				foreach ($cur_category->pages as $cur_page) {
					if (!isset($cur_page) || !$cur_page->ready())
						continue;
					// It's part of another menu.
					$pines->menu->menu_arrays[] = array(
						'path' => "{$path}/cat_{$cur_category->guid}/page_{$cur_page->guid}",
						'text' => $cur_page->name,
						'href' => array('com_content', 'page', array('a' => $cur_page->alias))
					);
				}
			}
			if ($cur_category->children)
				com_content__category_menu($cur_category, "{$path}/cat_{$cur_category->guid}");
		}
	}

	foreach ($categories as $cur_category) {
		if (!$cur_category->ready())
			continue;
		if (strpos($cur_category->menu_position, '/') === false) {
			// It's a new top level menu.
			$menu_position = "com_content_cat_{$cur_category->guid}";
			$pines->menu->menu_arrays[] = array(
				'path' => $menu_position,
				'text' => $cur_category->name,
				'position' => $cur_category->menu_position
			);
		} else {
			// It's part of another menu.
			$menu_position = $cur_category->menu_position;
			$cat_menu = array(
				'path' => $menu_position,
				'text' => $cur_category->name
			);
			if ($cur_category->get_option('link_menu'))
				$cat_menu['href'] = array('com_content', 'category', array('a' => $cur_category->alias));
			$pines->menu->menu_arrays[] = $cat_menu;
		}
		if ($cur_category->children)
			com_content__category_menu($cur_category, $menu_position);

		if ($cur_category->get_option('show_pages_in_menu')) {
			foreach ($cur_category->pages as $cur_page) {
				if (!isset($cur_page) || !$cur_page->ready())
					continue;
				// It's part of another menu.
				$pines->menu->menu_arrays[] = array(
					'path' => "{$menu_position}/page_{$cur_page->guid}",
					'text' => $cur_page->name,
					'href' => array('com_content', 'page', array('a' => $cur_page->alias))
				);
			}
		}
	}

	unset($categories, $cur_category, $module);
}

?>