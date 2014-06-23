<?php
/**
 * Front page.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

// Print the front page meta tags.
if ($pines->config->com_content->front_page_meta_tags)
	$module = new module('com_content', 'front_page_meta_tags', 'head');

if (!empty($pines->config->com_content->front_page_title))
	$pines->config->page_title = $pines->config->com_content->front_page_title;

// Set the variant for the front page.
if ($pines->config->com_content->front_page_variant && $pines->com_content->is_variant_valid($pines->config->com_content->front_page_variant)) {
	$cur_template = $pines->current_template;
	$pines->config->$cur_template->variant = $pines->config->com_content->front_page_variant;
}

// Determine which page.
$page_num = max(array(((int) $_REQUEST['page']) - 1, 0));

$time = time();
// If the default is to show pages on the front page, then pages where
// show_front_page is null are on the front page.
if ($pines->config->com_content->def_page_show_front_page) {
	$selector = array('!|',
			'strict' => array('show_front_page', false),
			'isset' => 'show_front_page'
		);
} else {
	$selector = array('&',
			'strict' => array('show_front_page', true)
		);
}

if ($pines->config->com_content->front_page_per_page === 0 || $pines->config->com_content->front_page_pagination_type == 'complete')
	$options = array('class' => com_content_page, 'reverse' => true, 'sort' => 'cdate');
else {
	$options = array(
		'class' => com_content_page,
		'offset' => $pines->config->com_content->front_page_per_page * $page_num,
		'limit' => $pines->config->com_content->front_page_per_page + 1,
		'reverse' => true,
		'sort' => 'cdate'
	);
}
// Gather all published pages on the front page.
$pages = $pines->entity_manager->get_entities(
		$options,
		array('&',
			'tag' => array('com_content', 'page'),
			'data' => array('enabled', true),
			'lte' => array('publish_begin', $time)
		),
		$selector,
		array('|',
			'data' => array('publish_end', null),
			'gt' => array('publish_end', $time)
		)
	);

if ($pines->config->com_content->front_page_per_page !== 0 && $pines->config->com_content->front_page_pagination_type == 'complete') {
	// Get only the pages on this page.
	$show_pages = array_slice($pages, $pines->config->com_content->front_page_per_page * $page_num, $pines->config->com_content->front_page_per_page);
} elseif ($pines->config->com_content->front_page_per_page !== 0 && count($pages) > $pines->config->com_content->front_page_per_page)
	$show_pages = array_slice($pages, 0, -1);
else
	$show_pages = $pages;

// Check that there are pages.
if (!$show_pages && $page_num > 0)
	throw new HttpClientException(null, 404);

// Print either the full page, or just the intro.
foreach ($show_pages as $cur_page) {
	if ($pines->config->com_content->front_page_full_pages)
		$cur_page->print_page();
	else
		$cur_page->print_intro();
}

// Do we need to show pagination?
if ($pines->config->com_content->front_page_per_page !== 0 && (count($pages) > $pines->config->com_content->front_page_per_page || $page_num > 0)) {
	// Show a pagination module.
	$pagination = new module('com_content', 'pagination', 'content');
	$pagination->type = $pines->config->com_content->front_page_pagination_type;
	$pagination->page = $page_num;
	if ($pines->config->com_content->front_page_pagination_type == 'complete')
		$pagination->pages = ceil(count($pages) / $pines->config->com_content->front_page_per_page);
	else
		$pagination->next_exists = count($pages) > $pines->config->com_content->front_page_per_page;
	if ($pines->config->default_component == 'com_content') {
		$pagination->no_page_url = pines_url();
		$pagination->page_url = pines_url(null, null, array('page' => '__page__'));
	} else {
		$pagination->no_page_url = pines_url('com_content');
		$pagination->page_url = pines_url('com_content', null, array('page' => '__page__'));
	}
}

?>