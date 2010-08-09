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
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editcategory') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'category/list'));
	$category = com_sales_category::factory((int) $_REQUEST['id']);
	if (!isset($category->guid)) {
		pines_error('Requested category id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newcategory') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'category/list'));
	$category = com_sales_category::factory();
}

$category->name = $_REQUEST['name'];
$category->enabled = ($_REQUEST['enabled'] == 'ON');
if ($pines->config->com_sales->com_storefront) {
	$category->show_menu = ($_REQUEST['show_menu'] == 'ON');
	$category->menu_position = $_REQUEST['menu_position'];
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
				$restricted = ($cur_spec->values[3] == 'Yes');
				$options = empty($cur_spec->values[4]) ? array() : explode(';;', $cur_spec->values[4]);
				break;
			case 'float':
				$restricted = ($cur_spec->values[3] == 'Yes');
				$options = empty($cur_spec->values[4]) ? array() : explode(';;', $cur_spec->values[4]);
				array_walk($options, 'floatval');
				break;
			case 'bool':
				$restricted = null;
				$options = array();
				break;
			case 'heading':
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
			'restricted' => $restricted,
			'options' => $options,
			'category' => $category
		);
	}
	$pines->com_sales->sort_specs($category->specs);
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

redirect(pines_url('com_sales', 'category/list'));

?>