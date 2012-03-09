<?php
/**
 * Save changes to a page.
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

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_content/editpage') )
		punt_user(null, pines_url('com_content', 'page/list'));
	$page = com_content_page::factory((int) $_REQUEST['id']);
	if (!isset($page->guid)) {
		pines_error('Requested page id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_content/newpage') )
		punt_user(null, pines_url('com_content', 'page/list'));
	$page = com_content_page::factory();
}

// General
$page->name = $_REQUEST['name'];
$page->alias = preg_replace('/[^\w\d-.]/', '', $_REQUEST['alias']);
$page->title_use_name = ($_REQUEST['title_use_name'] == 'ON');
$page->title = $_REQUEST['title'];
$page->title_position = ($_REQUEST['title_position'] == 'null' ? null : $_REQUEST['title_position']);
if (isset($page->title_position) && !in_array($page->title_position, array('prepend', 'append', 'replace')))
	$page->title_position = null;
$page->enabled = ($_REQUEST['enabled'] == 'ON');
$page->show_front_page = ($_REQUEST['show_front_page'] == 'null' ? null : ($_REQUEST['show_front_page'] == 'true'));
$page->content_tags = explode(',', $_REQUEST['content_tags']);
foreach ($page->content_tags as $key => $cur_tag) {
	if ($cur_tag == '')
		unset($page->content_tags[$key]);
}
// TODO: Use an HTML filter here.
$page->intro = $_REQUEST['intro'];
$page->content = $_REQUEST['content'];

// Page Head
if (gatekeeper('com_content/editmeta')) {
	$meta_tags = (array) json_decode($_REQUEST['meta_tags']);
	$page->meta_tags = array();
	foreach ($meta_tags as $cur_meta_tag) {
		if (!isset($cur_meta_tag->values[0], $cur_meta_tag->values[1]))
			continue;
		$page->meta_tags[] = array('name' => $cur_meta_tag->values[0], 'content' => $cur_meta_tag->values[1]);
	}
}
if ($pines->config->com_content->custom_head && gatekeeper('com_content/edithead')) {
	$page->enable_custom_head = ($_REQUEST['enable_custom_head'] == 'ON');
	$page->custom_head = $_REQUEST['custom_head'];
}

// Menu
$page->com_menueditor_entries = json_decode($_REQUEST['com_menueditor_entries'], true);

// Conditions
$conditions = (array) json_decode($_REQUEST['conditions']);
$page->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$page->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

// Advanced
if (!empty($_REQUEST['p_cdate']))
	$page->p_cdate = strtotime($_REQUEST['p_cdate']);
if (!empty($_REQUEST['p_mdate']))
	$page->p_mdate = strtotime($_REQUEST['p_mdate']);
$page->publish_begin = strtotime($_REQUEST['publish_begin']);
if (!empty($_REQUEST['publish_end']))
	$page->publish_end = strtotime($_REQUEST['publish_end']);
else
	$page->publish_end = null;
$page->show_title = ($_REQUEST['show_title'] == 'null' ? null : ($_REQUEST['show_title'] == 'true'));
$page->show_author_info = ($_REQUEST['show_author_info'] == 'null' ? null : ($_REQUEST['show_author_info'] == 'true'));
$page->show_content_in_list = ($_REQUEST['show_content_in_list'] == 'null' ? null : ($_REQUEST['show_content_in_list'] == 'true'));
$page->show_intro = ($_REQUEST['show_intro'] == 'null' ? null : ($_REQUEST['show_intro'] == 'true'));
$page->show_breadcrumbs = ($_REQUEST['show_breadcrumbs'] == 'null' ? null : ($_REQUEST['show_breadcrumbs'] == 'true'));
$page->variants = array();
foreach ($_REQUEST['variants'] as $cur_variant_entry) {
	list ($cur_template, $cur_variant) = explode('::', $cur_variant_entry, 2);
	if (!$pines->com_content->is_variant_valid($cur_variant, $cur_template)) {
		pines_notice("The variant \"$cur_variant\" is not a valid variant of the template \"$cur_template\". It is being skipped.");
		continue;
	}
	$page->variants[$cur_template] = $cur_variant;
}

if (empty($page->name)) {
	$page->print_form();
	pines_notice('Please specify a name.');
	return;
}
if (empty($page->alias)) {
	$page->print_form();
	pines_notice('Please specify an alias.');
	return;
}

$test = $pines->entity_manager->get_entity(array('class' => com_content_page, 'skip_ac' => true), array('&', 'tag' => array('com_content', 'page'), 'data' => array('alias', $page->alias)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$page->print_form();
	pines_notice('There is already an page with that alias. Please choose a different alias.');
	return;
}

if (!$pines->com_menueditor->check_entries($page->com_menueditor_entries)) {
	$page->print_form();
	return;
}

$page->ac->group = $pines->config->com_content->ac_page_group;
$page->ac->other = $pines->config->com_content->ac_page_other;

if ($page->save()) {
	pines_notice('Saved page ['.$page->name.']');
	// Assign the page to the selected categories.
	// We have to do this here, because new pages won't have a GUID until now.
	$categories = array_map('intval', (array) $_REQUEST['categories']);
	$all_categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'tag' => array('com_content', 'category'), 'data' => array('enabled', true)));
	foreach($all_categories as &$cur_cat) {
		if (in_array($cur_cat->guid, $categories) && !$page->in_array($cur_cat->pages)) {
			$cur_cat->pages[] = $page;
			if (!$cur_cat->save())
				pines_error("Couldn't add page to category {$cur_cat->name}. Do you have permission?");
		} elseif (!in_array($cur_cat->guid, $categories) && $page->in_array($cur_cat->pages)) {
			$key = $page->array_search($cur_cat->pages);
			unset($cur_cat->pages[$key]);
			if (!$cur_cat->save())
				pines_error("Couldn't remove page from category {$cur_cat->name}. Do you have permission?");
		}
	}
	unset($cur_cat);
} else {
	pines_error('Error saving page. Do you have permission?');
}

pines_redirect(pines_url('com_content', 'page/list'));

?>