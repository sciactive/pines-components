<?php
/**
 * Save changes to a category.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editcategory') )
		punt_user(null, pines_url('com_sales', 'category/list'));
	$category = com_sales_category::factory((int) $_REQUEST['id']);
	if (!isset($category->guid)) {
		pines_error('Requested category id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newcategory') )
		punt_user(null, pines_url('com_sales', 'category/list'));
	$category = com_sales_category::factory();
}

// General
$category->name = $_REQUEST['name'];
$category->enabled = ($_REQUEST['enabled'] == 'ON');

if ($pines->config->com_sales->com_storefront) {
	// Storefront
	$category->alias = preg_replace('/[^\w\d-.]/', '', $_REQUEST['alias']);
	$category->show_title = ($_REQUEST['show_title'] == 'ON');
	$category->show_breadcrumbs = ($_REQUEST['show_breadcrumbs'] == 'ON');
	$category->show_menu = ($_REQUEST['show_menu'] == 'ON');
	$category->menu_position = $_REQUEST['menu_position'];
	$category->show_children = ($_REQUEST['show_children'] == 'ON');
	$category->show_pages = array();
	foreach ((array) $_REQUEST['show_pages'] as $cur_page_guid) {
		$cur_page = com_content_page::factory((int) $cur_page_guid);
		if (isset($cur_page->guid))
			$category->show_pages[] = $cur_page;
	}
	$category->show_products = ($_REQUEST['show_products'] == 'ON');
	$category->description = $_REQUEST['description'];
	$category->specs = array();
	$specs = (array) json_decode($_REQUEST['specs']);
	foreach ($specs as $cur_spec) {
		if (empty($cur_spec->values[1]))
			continue;
		$key = $cur_spec->key;
		if (empty($key) || $key == 'null')
			$key = uniqid('spec_');
		switch ($cur_spec->values[2]) {
			case 'string':
				$show_filter = ($cur_spec->values[3] == 'Yes');
				$restricted = ($cur_spec->values[4] == 'Yes');
				$options = empty($cur_spec->values[5]) ? array() : explode(';;', $cur_spec->values[5]);
				break;
			case 'float':
				$show_filter = ($cur_spec->values[3] == 'Yes');
				$restricted = ($cur_spec->values[4] == 'Yes');
				$options = empty($cur_spec->values[5]) ? array() : explode(';;', $cur_spec->values[5]);
				foreach ($options as &$cur_option) {
					$cur_option = (float) $cur_option;
				}
				unset($cur_option);
				break;
			case 'bool':
				$show_filter = ($cur_spec->values[3] == 'Yes');
				$restricted = null;
				$options = array();
				break;
			case 'heading':
				$show_filter = null;
				$restricted = null;
				$options = array();
				break;
			default:
				continue 2;
				break;
		}
		$category->specs[$key] = array(
			'order' => $cur_spec->values[0],
			'name' => $cur_spec->values[1],
			'type' => $cur_spec->values[2],
			'show_filter' => $show_filter,
			'restricted' => $restricted,
			'options' => $options,
			'category' => $category
		);
	}
	$pines->com_sales->sort_specs($category->specs);

	// Page Head
	$category->title_use_name = ($_REQUEST['title_use_name'] == 'ON');
	$category->title = $_REQUEST['title'];
	$category->title_position = $_REQUEST['title_position'];
	if (!in_array($category->title_position, array('prepend', 'append', 'replace')))
		$category->title_position = 'prepend';
	$meta_tags = (array) json_decode($_REQUEST['meta_tags']);
	$category->meta_tags = array();
	foreach ($meta_tags as $cur_meta_tag) {
		if (!isset($cur_meta_tag->values[0], $cur_meta_tag->values[1]))
			continue;
		$category->meta_tags[] = array('name' => $cur_meta_tag->values[0], 'content' => $cur_meta_tag->values[1]);
	}
}

// Do the check now in case the parent category is saved.
if (empty($category->name)) {
	$category->print_form();
	pines_notice('Please specify a name.');
	return;
}

$category->ac->other = 1;

if ((int) $_REQUEST['parent'] === $category->guid) {
	$category->print_form();
	pines_notice('Cannot make a category its own parent.');
	return;
}

if ((int) $_REQUEST['parent'] != $category->parent->guid) {
	// The category has a different parent.
	if (isset($category->parent)) {
		// Remove the category from its parent.
		$key = $category->array_search($category->parent->children);
		if ($key !== false)
			unset($category->parent->children[$key]);
		if ($category->parent->save() && $category->save()) {
			$category->parent = null;
		} else {
			$category->print_form();
			pines_notice('Could not remove category from its parent.');
			return;
		}
	}
	if ($_REQUEST['parent'] != 'null') {
		if (!isset($category->guid) && !$category->save()) {
			$category->print_form();
			pines_notice('Could not save category.');
			return;
		}
		// Add the category to the parent.
		$category->parent = com_sales_category::factory((int) $_REQUEST['parent']);
		if (!isset($category->parent->guid)) {
			$category->parent = null;
			$category->print_form();
			pines_notice('Could not find specified parent.');
			return;
		}
		$category->parent->children[] = $category;
		if (!$category->parent->save()) {
			$category->print_form();
			pines_notice('Could not save category in the specified parent.');
			return;
		}
	}
}

foreach ($category->children as $key => &$cur_child) {
	if (!isset($cur_child->guid))
		unset($category->children[$key]);
}
unset($cur_child);

if ($category->save()) {
	pines_notice('Saved category ['.$category->name.']');
} else {
	pines_error('Error saving category. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'category/list'));

?>