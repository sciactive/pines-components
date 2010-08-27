<?php
/**
 * Save changes to an page.
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
	if ( !gatekeeper('com_content/editpage') )
		punt_user('You don\'t have necessary permission.', pines_url('com_content', 'page/list'));
	$page = com_content_page::factory((int) $_REQUEST['id']);
	if (!isset($page->guid)) {
		pines_error('Requested page id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_content/newpage') )
		punt_user('You don\'t have necessary permission.', pines_url('com_content', 'page/list'));
	$page = com_content_page::factory();
}

// General
$page->name = $_REQUEST['name'];
$page->alias = preg_replace('/[^\w\d-.]/', '', $_REQUEST['alias']);
$page->enabled = ($_REQUEST['enabled'] == 'ON');
$page->show_front_page = ($_REQUEST['show_front_page'] == 'ON');
$page->content_tags = explode(',', $_REQUEST['content_tags']);
// TODO: Use an HTML filter here.
$page->intro = $_REQUEST['intro'];
$page->content = $_REQUEST['content'];

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
$page->show_intro = ($_REQUEST['show_intro'] == 'ON');
$page->show_title = ($_REQUEST['show_title'] == 'ON');
$page->show_menu = ($_REQUEST['show_menu'] == 'ON');
$page->menu_position = $_REQUEST['menu_position'];

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

$test = $pines->entity_manager->get_entity(array('class' => com_content_page, 'skip_ac' => true), array('&', 'data' => array('alias', $page->alias), 'tag' => array('com_content', 'page')));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$page->print_form();
	pines_notice('There is already an page with that alias. Please choose a different alias.');
	return;
}

$page->ac->group = $pines->config->com_content->ac_page_group;
$page->ac->other = $pines->config->com_content->ac_page_other;

if ($page->save()) {
	pines_notice('Saved page ['.$page->name.']');
	// Assign the page to the selected categories.
	// We have to do this here, because new pages won't have a GUID until now.
	$categories = array_map('intval', (array) $_REQUEST['categories']);
	$all_categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'data' => array('enabled', true), 'tag' => array('com_content', 'category')));
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

redirect(pines_url('com_content', 'page/list'));

?>