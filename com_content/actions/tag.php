<?php
/**
 * Show pages with requested tag(s).
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

// Get all the pages that have the requested tags.
$tags = explode(',', $_REQUEST['a']);
if (!$_REQUEST['a'] || !$tags)
	return 'error_404';
$selector = array('&',
		'tag' => array('com_content', 'page'),
		'strict' => array('enabled', true),
		'array' => array()
	);
foreach ($tags as $cur_tag)
	$selector['array'][] = array('content_tags', $cur_tag);

$entities = $pines->entity_manager->get_entities(
		array('class' => com_content_page),
		$selector
	);

// Now determine that each one is ready to print.
foreach ($entities as $key => $cur_entity) {
	if (!$cur_entity->ready())
		unset($entities[$key]);
}

if (!$entities)
	return 'error_404';

// Set the default variant for categories.
if ($pines->config->com_content->cat_variant && $pines->com_content->is_variant_valid($pines->config->com_content->cat_variant)) {
	$cur_template = $pines->current_template;
	$pines->config->$cur_template->variant = $pines->config->com_content->cat_variant;
}

// Page title.
$pines->page->title_pre('Pages Tagged '.implode(', ', $tags).' - ');

$module = new module('com_content', 'breadcrumb', 'breadcrumbs');
$module->tags = $tags;

// Print the pages.
foreach ($entities as $cur_page) {
	if (!isset($cur_page))
		continue;
	$module = $cur_page->print_intro();
}

?>