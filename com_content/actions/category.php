<?php
/**
 * Show a category's pages.
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

if (!empty($_REQUEST['id'])) {
	$entity = com_content_category::factory((int) $_REQUEST['id']);
} else {
	$entity = $pines->entity_manager->get_entity(
			array('class' => com_content_category),
			array('&',
				'tag' => array('com_content', 'category'),
				'strict' => array('alias', $_REQUEST['a'])
			)
		);
}

if (!isset($entity->guid) || !$entity->ready())
	return 'error_404';

// Set the default variant for categories.
if ($pines->config->com_content->cat_variant && $pines->com_content->is_variant_valid($pines->config->com_content->cat_variant)) {
	$cur_template = $pines->current_template;
	$pines->config->$cur_template->variant = $pines->config->com_content->cat_variant;
}

// Check for and set the variant for the current template.
if (isset($entity->variants[$pines->current_template]) && $pines->com_content->is_variant_valid($entity->variants[$pines->current_template])) {
	$cur_template = $pines->current_template;
	$pines->config->$cur_template->variant = $entity->variants[$pines->current_template];
}

// Page title.
$pines->page->title_pre("$entity->name - ");

// Meta tags.
if ($entity->meta_tags) {
	$module = new module('com_content', 'meta_tags', 'head');
	$module->entity = $entity;
}

if ($entity->get_option('show_breadcrumbs')) {
	$module = new module('com_content', 'breadcrumb', 'breadcrumbs');
	$module->entity = $entity;
}

$entity->print_category();

?>