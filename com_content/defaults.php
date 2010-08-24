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
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'show_cat_menus',
		'cname' => 'Show Category Menus',
		'description' => 'Show categories configured in the menu.',
		'value' => true,
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
		'value' => 0,
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
);

?>