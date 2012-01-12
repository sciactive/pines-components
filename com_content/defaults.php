<?php
/**
 * com_content's configuration defaults.
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

return array(
	array(
		'name' => 'front_page_variant',
		'cname' => 'Front Page Variant',
		'description' => "The page variant to use on the front page. See your template's configuration for the available variants. Leave blank for no change.\nNot all templates have page variants.",
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'front_page_full_pages',
		'cname' => 'Show Full Pages on Front Page',
		'description' => 'Show the full content of pages on the front page.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'front_page_meta_tags',
		'cname' => 'Front Page Meta Tags',
		'description' => 'Meta tags for the front page. The format is name:content.',
		'value' => array('robots:all'),
		'peruser' => true,
	),
	array(
		'name' => 'global_meta_tags',
		'cname' => 'Global Meta Tags',
		'description' => 'Meta tags for every page. The format is name:content.',
		'value' => array('generator:Pines CMS'),
		'peruser' => true,
	),
	array(
		'name' => 'cat_variant',
		'cname' => 'Category Variant',
		'description' => "The page variant to use as a default on categies. Category specific settings will override this. (Also applies to tag search pages.)",
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'show_cat_menus',
		'cname' => 'Show Category Menus',
		'description' => 'Show categories configured in the menu.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'page_variant',
		'cname' => 'Page Variant',
		'description' => "The page variant to use as a default on pages. Page specific settings will override this.",
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'show_page_menus',
		'cname' => 'Show Page Menus',
		'description' => 'Show pages configured in the menu.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'ac_page_group',
		'cname' => 'Page Group Access',
		'description' => 'The level of access the user\'s primary group has to their pages. This will be applied when saving pages.',
		'value' => 2,
		'options' => array(
			'None' => 0,
			'Read Only' => 1,
			'Read/Write' => 2,
			'Read/Write/Delete' => 3
		),
		'peruser' => true,
	),
	array(
		'name' => 'ac_page_other',
		'cname' => 'Page Other Access',
		'description' => 'The level of access other users have to pages. This will be applied when saving pages.',
		'value' => 1,
		'options' => array(
			'None' => 0,
			'Read Only' => 1,
			'Read/Write' => 2,
			'Read/Write/Delete' => 3
		),
		'peruser' => true,
	),
	array(
		'name' => 'wrap_pages',
		'cname' => 'Wrap Pages',
		'description' => 'Wrap pages in a relative positioned div, so absolute positioned content will appear correctly.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'show_tags_in_lists',
		'cname' => 'Show Tags in Lists',
		'description' => 'Show content tags on lists, like category pages.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'tags_position',
		'cname' => 'Tags Position',
		'description' => 'Where to place the content tags on pages.',
		'value' => 'after',
		'options' => array(
			'Before Content' => 'before',
			'After Content' => 'after'
		),
		'peruser' => true,
	),
	array(
		'name' => 'tags_text',
		'cname' => 'Tags Text',
		'description' => 'The text to print before the tags.',
		'value' => 'Tags: ',
		'peruser' => true,
	),
	array(
		'name' => 'custom_head',
		'cname' => 'Custom Head Code',
		'description' => 'Allow custom code to be placed in the head tag of the page. This introduces security vulnerabilities, so be careful!',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'custom_css',
		'cname' => 'Custom Stylesheets',
		'description' => 'Any stylesheets that match these patterns will be included when a content page is printed. (If it isn\'t there already, ".css" will be appended to the end of each pattern.)',
		'value' => array(
			'media/css/*.css'
		),
		'peruser' => true,
	),
	array(
		'name' => 'replace_static',
		'cname' => 'Replace Paths',
		'description' => 'Replace paths to the upload URL (like image sources) with static path. This applies to all components that print formatted content. It only applies when a static path is set.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'def_cat_title_position',
		'cname' => 'Defaults: Categories: Title Position',
		'description' => '',
		'value' => 'prepend',
		'options' => array(
			'Prepend to Site Title' => 'prepend',
			'Append to Site Title' => 'append',
			'Replace Site Title' => 'replace',
		),
		'peruser' => true,
	),
	array(
		'name' => 'def_cat_show_pages_in_menu',
		'cname' => 'Defaults: Categories: Show Pages in Menu',
		'description' => '',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'def_cat_link_menu',
		'cname' => 'Defaults: Categories: Link in Menu',
		'description' => '',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'def_cat_show_title',
		'cname' => 'Defaults: Categories: Show Title',
		'description' => '',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'def_cat_show_breadcrumbs',
		'cname' => 'Defaults: Categories: Show Breadcrumbs',
		'description' => '',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'def_page_title_position',
		'cname' => 'Defaults: Pages: Title Position',
		'description' => '',
		'value' => 'prepend',
		'options' => array(
			'Prepend to Site Title' => 'prepend',
			'Append to Site Title' => 'append',
			'Replace Site Title' => 'replace',
		),
		'peruser' => true,
	),
	array(
		'name' => 'def_page_show_front_page',
		'cname' => 'Defaults: Pages: Show on Front Page',
		'description' => '',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'def_page_show_title',
		'cname' => 'Defaults: Pages: Show Title',
		'description' => '',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'def_page_show_author_info',
		'cname' => 'Defaults: Pages: Show Author Info',
		'description' => '',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'def_page_show_content_in_list',
		'cname' => 'Defaults: Pages: Show Full Content in List',
		'description' => '',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'def_page_show_intro',
		'cname' => 'Defaults: Pages: Show Intro on Page',
		'description' => '',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'def_page_show_breadcrumbs',
		'cname' => 'Defaults: Pages: Show Breadcrumbs',
		'description' => '',
		'value' => true,
		'peruser' => true,
	),
);

?>