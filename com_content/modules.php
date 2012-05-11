<?php
/**
 * com_content's modules.
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
	'quickpage' => array(
		'cname' => 'Quick Page',
		'description' => 'Quickly create and publish content pages.',
		//'image' => 'includes/page_widget_screen.png',
		'view' => 'modules/quickpage',
		'form' => 'modules/quickpage_form',
		'type' => 'widget',
		'widget' => array(
			'default' => true,
			'depends' => array(
				'ability' => 'com_content/newpage',
			),
		),
	),
	'page' => array(
		'cname' => 'Page Content',
		'description' => 'Show the content of a page.',
		'image' => 'includes/page_widget_screen.png',
		'view' => 'page/page',
		'form' => 'modules/page_form',
		'type' => 'module imodule widget',
		'widget' => array(
			'default' => false,
			'depends' => array(
				'ability' => 'com_content/pagemodule',
			),
		),
	),
	'category' => array(
		'cname' => 'Category Listing',
		'description' => 'Show a listing of a category.',
		'image' => 'includes/category_widget_screen.png',
		'view' => 'category/category',
		'form' => 'modules/category_form',
		'type' => 'module imodule widget',
		'widget' => array(
			'default' => false,
			'depends' => array(
				'ability' => 'com_content/categorymodule',
			),
		),
	),
	'content' => array(
		'cname' => 'Custom Content (HTML)',
		'description' => 'Show any custom HTML.',
		'view' => 'modules/custom',
		'form' => 'modules/custom_form',
		'type' => 'module',
	),
);

?>