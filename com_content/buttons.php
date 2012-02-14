<?php
/**
 * com_content's buttons.
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
	'categories' => array(
		'description' => 'Category list.',
		'text' => 'Categories',
		'class' => 'picon-folder-html',
		'href' => pines_url('com_content', 'category/list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_content/listcategories',
		),
	),
	'category_new' => array(
		'description' => 'New category.',
		'text' => 'Category',
		'class' => 'picon-folder-new',
		'href' => pines_url('com_content', 'category/edit'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_content/newcategory',
		),
	),
	'pages' => array(
		'description' => 'Page list.',
		'text' => 'Pages',
		'class' => 'picon-document-multiple',
		'href' => pines_url('com_content', 'page/list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_content/listpages',
		),
	),
	'page_new' => array(
		'description' => 'New page.',
		'text' => 'Page',
		'class' => 'picon-document-new',
		'href' => pines_url('com_content', 'page/edit'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_content/newpage',
		),
	),
);

?>