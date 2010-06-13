<?php
/**
 * com_content's information.
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
	'name' => 'CMS',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Content Management System',
	'description' => 'Manage content articles.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'user_manager&entity_manager&editor',
		'component' => 'com_jquery&com_pgrid&com_ptags'
	),
	'abilities' => array(
		array('listarticles', 'List Articles', 'User can see articles.'),
		array('newarticle', 'Create Articles', 'User can create new articles.'),
		array('editarticle', 'Edit Articles', 'User can edit current articles.'),
		array('deletearticle', 'Delete Articles', 'User can delete current articles.'),
		array('listcategories', 'List Categories', 'User can see categories. (Not needed to assign categories for articles.)'),
		array('newcategory', 'Create Categories', 'User can create new categories.'),
		array('editcategory', 'Edit Categories', 'User can edit current categories.'),
		array('deletecategory', 'Delete Categories', 'User can delete current categories.')
	),
);

?>