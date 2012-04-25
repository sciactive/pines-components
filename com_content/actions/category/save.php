<?php
/**
 * Save changes to a category.
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

// General
$category->name = $_REQUEST['name'];
$category->alias = preg_replace('/[^\w\d-.]/', '', $_REQUEST['alias']);
$category->title_use_name = ($_REQUEST['title_use_name'] == 'ON');
$category->title = $_REQUEST['title'];
$category->title_position = ($_REQUEST['title_position'] == 'null' ? null : $_REQUEST['title_position']);
if (isset($category->title_position) && !in_array($category->title_position, array('prepend', 'append', 'replace')))
	$category->title_position = null;
$category->enabled = ($_REQUEST['enabled'] == 'ON');
$category->show_menu = ($_REQUEST['show_menu'] == 'ON');
$category->menu_position = $_REQUEST['menu_position'];
$category->show_pages_in_menu = ($_REQUEST['show_pages_in_menu'] == 'null' ? null : ($_REQUEST['show_pages_in_menu'] == 'true'));
$category->link_menu = ($_REQUEST['link_menu'] == 'null' ? null : ($_REQUEST['link_menu'] == 'true'));
$category->pages = array();
$pages = (array) json_decode($_REQUEST['pages']);
foreach ($pages as $cur_page_guid) {
	$cur_page = com_content_page::factory((int) $cur_page_guid);
	if (!isset($cur_page->guid)) {
		pines_notice("Invalid page id [{$cur_page_guid}].");
		continue;
	}
	$category->pages[] = $cur_page;
}

// Page Head
if (gatekeeper('com_content/editmeta')) {
	$meta_tags = (array) json_decode($_REQUEST['meta_tags']);
	$category->meta_tags = array();
	foreach ($meta_tags as $cur_meta_tag) {
		if (!isset($cur_meta_tag->values[0], $cur_meta_tag->values[1]))
			continue;
		$category->meta_tags[] = array('name' => $cur_meta_tag->values[0], 'content' => $cur_meta_tag->values[1]);
	}
}
if ($pines->config->com_content->custom_head && gatekeeper('com_content/edithead')) {
	$category->enable_custom_head = ($_REQUEST['enable_custom_head'] == 'ON');
	$category->custom_head = $_REQUEST['custom_head'];
}

// Menu
$category->com_menueditor_entries = json_decode($_REQUEST['com_menueditor_entries'], true);

// Page
$category->show_title = ($_REQUEST['show_title'] == 'null' ? null : ($_REQUEST['show_title'] == 'true'));
$category->show_breadcrumbs = ($_REQUEST['show_breadcrumbs'] == 'null' ? null : ($_REQUEST['show_breadcrumbs'] == 'true'));
$category->intro = $_REQUEST['intro'];
$category->variants = array();
foreach ($_REQUEST['variants'] as $cur_variant_entry) {
	list ($cur_template, $cur_variant) = explode('::', $cur_variant_entry, 2);
	if (!$pines->com_content->is_variant_valid($cur_variant, $cur_template)) {
		pines_notice("The variant \"$cur_variant\" is not a valid variant of the template \"$cur_template\". It is being skipped.");
		continue;
	}
	$category->variants[$cur_template] = $cur_variant;
}

// Conditions
$conditions = (array) json_decode($_REQUEST['conditions']);
$category->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$category->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

// Run through children and make sure there are no null entries.
foreach ($category->children as $key => $cur_child) {
	if (!isset($cur_child))
		unset($category->children[$key]);
}

// Do the check now in case the parent category is saved.
if (empty($category->name)) {
	$category->print_form();
	pines_notice('Please specify a name.');
	return;
}

$test = $pines->entity_manager->get_entity(array('class' => com_content_category, 'skip_ac' => true), array('&', 'tag' => array('com_content', 'category'), 'data' => array('alias', $category->alias)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$category->print_form();
	pines_notice('There is already an category with that alias. Please choose a different alias.');
	return;
}

if (!$pines->com_menueditor->check_entries($category->com_menueditor_entries)) {
	$category->print_form();
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

pines_redirect(pines_url('com_content', 'category/list'));

?>