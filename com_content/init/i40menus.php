<?php
/**
 * Show page and category menus.
 *
 * @package Components
 * @subpackage content
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
				'strict' => array('enabled', true),
				'isset' => 'com_menueditor_entries'
			)
		);

	// Add menus for all pages that have menu entries.
	foreach ($pages as $cur_page) {
		if (!$cur_page->ready() || !$cur_page->com_menueditor_entries)
			continue;
		$pines->com_menueditor->add_entries($cur_page->com_menueditor_entries, array('link' => array('com_content', 'page', array('a' => $cur_page->alias))));
	}
	unset($pages, $cur_page);
}

if ($pines->config->com_content->show_cat_menus) {
	$categories = (array) $pines->entity_manager->get_entities(
			array('class' => com_content_category),
			array('&',
				'tag' => array('com_content', 'category'),
				'strict' => array('enabled', true),
				'isset' => 'com_menueditor_entries'
			)
		);

	/**
	 * Add a category's children to the menu.
	 * @param com_content_category $category The category.
	 * @param array $paths The menu paths.
	 */
	function com_content__category_menu($category, $paths) {
		global $pines;
		if (!$category->children)
			return;
		foreach ($category->children as $cur_category) {
			if (!isset($cur_category) || !$cur_category->ready())
				continue;
			$cat_menu = array(
				'text' => $cur_category->name
			);
			if ($cur_category->get_option('link_menu'))
				$cat_menu['href'] = array('com_content', 'category', array('a' => $cur_category->alias));
			$show_pages = $cur_category->get_option('show_pages_in_menu');
			// Go through each path and add the entry and pages.
			foreach ($paths as &$cur_path) {
				// Update the path.
				$cur_path .= "/cat_{$cur_category->guid}";
				$cat_menu['path'] = $cur_path;
				$pines->menu->menu_arrays[] = $cat_menu;
				if ($show_pages) {
					foreach ($cur_category->pages as $cur_page) {
						if (!isset($cur_page) || !$cur_page->ready())
							continue;
						// It's part of another menu.
						$pines->menu->menu_arrays[] = array(
							'path' => "$cur_path/page_{$cur_page->guid}",
							'text' => $cur_page->name,
							'href' => array('com_content', 'page', array('a' => $cur_page->alias))
						);
					}
				}
			}
			unset($cur_path);

			if ($cur_category->children)
				com_content__category_menu($cur_category, $paths);
		}
	}

	// Add menus for all categories that have menu entries.
	foreach ($categories as $cur_category) {
		if (!$cur_category->ready() || !$cur_category->com_menueditor_entries)
			continue;
		// The overrides link should only be filled if the menus are supposed to be linked.
		if ($cur_category->get_option('link_menu'))
			$overrides = array('link' => array('com_content', 'category', array('a' => $cur_category->alias)));
		else
			$overrides = array();
		// Remember the arrays that were added.
		$menu_arrays = $pines->com_menueditor->add_entries($cur_category->com_menueditor_entries, $overrides);
		if ($cur_category->get_option('show_pages_in_menu')) {
			// Add pages to the menu.
			foreach ($cur_category->pages as $cur_page) {
				if (!isset($cur_page) || !$cur_page->ready())
					continue;
				// Add the page to all the menus.
				foreach ($menu_arrays as $cur_array) {
					$pines->menu->menu_arrays[] = array(
						'path' => "{$cur_array['path']}/page_{$cur_page->guid}",
						'text' => $cur_page->name,
						'href' => array('com_content', 'page', array('a' => $cur_page->alias))
					);
				}
			}
		}
		// And add child categories.
		if ($cur_category->children) {
			$paths = array();
			foreach ($menu_arrays as $cur_array) {
				$paths[] = $cur_array['path'];
			}
			com_content__category_menu($cur_category, $paths);
		}
	}
	unset($categories, $cur_category, $paths, $menu_arrays);
}

?>