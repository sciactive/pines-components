<?php
/**
 * Show category menus.
 *
 * @package Components\storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_storefront->show_categories)
	return;

if (XMLREQUEST)
	return;

$categories = (array) $pines->entity_manager->get_entities(
		array('class' => com_sales_category),
		array('&',
			'tag' => array('com_sales', 'category'),
			'strict' => array(
				array('enabled', true),
				array('show_menu', true)
			)
		)
	);

if (!$categories) {
	unset($categories);
	return;
}

/**
 * Add a category's children to the menu.
 * @param com_sales_category $category The category.
 * @param string $path The menu path.
 */
function com_storefront__category_menu($category, $path) {
	global $pines;
	if (!$category->children)
		return;
	foreach ($category->children as $cur_category) {
		if (!isset($cur_category) || !$cur_category->enabled)
			continue;
		$pines->menu->menu_arrays[] = array(
			'path' => "{$path}/cat_{$cur_category->guid}",
			'text' => $cur_category->name,
			'href' => array('com_storefront', 'category/browse', array('a' => $cur_category->alias))
		);
		if ($cur_category->children)
			com_storefront__category_menu($cur_category, "{$path}/cat_{$cur_category->guid}");
	}
}

foreach ($categories as $cur_category) {
	if (!isset($cur_category) || !$cur_category->enabled)
		continue;
	// module style
	//$module = new module('com_storefront', 'category/menu', $cur_category->menu_position);
	//$module->entity = $cur_category;

	// menu style
	if (strpos($cur_category->menu_position, '/') === false) {
		// It's a new top level menu.
		$menu_position = "com_storefront_cat_{$cur_category->guid}";
		$pines->menu->menu_arrays[] = array(
			'path' => "com_storefront_cat_{$cur_category->guid}",
			'text' => $cur_category->name,
			'position' => $cur_category->menu_position
		);
	} else {
		// It's part of another menu.
		$menu_position = $cur_category->menu_position;
		$pines->menu->menu_arrays[] = array(
			'path' => $cur_category->menu_position,
			'text' => $cur_category->name,
			'href' => array('com_storefront', 'category/browse', array('a' => $cur_category->alias))
		);
	}
	if ($cur_category->children)
		com_storefront__category_menu($cur_category, $menu_position);
}

unset($categories, $cur_category, $module);

?>