<?php
/**
 * Front page.
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

// Print the front page meta tags.
if ($pines->config->com_content->front_page_meta_tags)
	$module = new module('com_content', 'front_page_meta_tags', 'head');

// Set the variant for the front page.
if ($pines->config->com_content->front_page_variant && $pines->com_content->is_variant_valid($pines->config->com_content->front_page_variant)) {
	$cur_template = $pines->current_template;
	$pines->config->$cur_template->variant = $pines->config->com_content->front_page_variant;
}

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
// Gather all published pages on the front page.
$pages = $pines->entity_manager->get_entities(
		array('class' => com_content_page),
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

// Print either the full page, or just the intro.
foreach ($pages as $cur_page) {
	if ($pines->config->com_content->front_page_full_pages)
		$cur_page->print_page();
	else
		$cur_page->print_intro();
}

?>