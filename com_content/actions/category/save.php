<?php
/**
 * Save changes to a category.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_content/editcategory') )
		punt_user(null, pines_url('com_content', 'category/list'));
	$category = com_content_category::factory((int) $_REQUEST['id']);
	if (!isset($category->guid)) {
		pines_error('Requested category id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_content/newcategory') )
		punt_user(null, pines_url('com_content', 'category/list'));
	$category = com_content_category::factory();
}

$category->name = $_REQUEST['name'];
$category->enabled = ($_REQUEST['enabled'] == 'ON');
$category->show_menu = ($_REQUEST['show_menu'] == 'ON');
$category->menu_position = $_REQUEST['menu_position'];
$category->show_pages_in_menu = ($_REQUEST['show_pages_in_menu'] == 'ON');
$category->show_breadcrumbs = ($_REQUEST['show_breadcrumbs'] == 'ON');

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
		$category->parent = com_content_category::factory((int) $_REQUEST['parent']);
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

redirect(pines_url('com_content', 'category/list'));

?>