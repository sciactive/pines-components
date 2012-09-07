<?php
/**
 * com_content's information.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'CMS',
	'author' => 'SciActive',
	'version' => '1.2.0beta2',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Content Management System',
	'description' => 'Manage content pages.',
	'depend' => array(
		'pines' => '<3',
		'service' => 'user_manager&entity_manager&editor',
		'component' => 'com_jquery&com_bootstrap&com_pgrid&com_markdown&com_ptags&com_pform&com_menueditor'
	),
	'abilities' => array(
		array('listpages', 'List Pages', 'User can see pages.'),
		array('newpage', 'Create Pages', 'User can create new pages.'),
		array('editpage', 'Edit Pages', 'User can edit current pages.'),
		array('deletepage', 'Delete Pages', 'User can delete current pages.'),
		array('pagemodule', 'View Page Module', 'User can see and use the page module/widget.'),
		array('listcategories', 'List Categories', 'User can see categories. (Not needed to assign categories for pages.)'),
		array('newcategory', 'Create Categories', 'User can create new categories.'),
		array('editcategory', 'Edit Categories', 'User can edit current categories.'),
		array('deletecategory', 'Delete Categories', 'User can delete current categories.'),
		array('categorymodule', 'View Category Module', 'User can see and use the category module/widget.'),
		array('editmeta', 'Edit Meta Tags', 'User can edit pages\' and categories\' meta tags.'),
		array('edithead', 'Edit Custom Head Code', 'User can edit pages\' and categories\' custom head code.')
	),
);

?>