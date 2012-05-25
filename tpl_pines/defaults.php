<?php
/**
 * tpl_pines' configuration.
 *
 * @package Templates\pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

pines_session();

return array(
	array(
		'name' => 'variant',
		'cname' => 'Page Variant/Layout',
		'description' => 'The layout of the page. On two column layouts, the sidebars are combined into one. On full page, the sidebars are not available.',
		'value' => 'twocol-sideright',
		'options' => array(
			'threecol (Three columns.)' => 'threecol',
			'twocol-sideleft (Two columns, left sidebar.)' => 'twocol-sideleft',
			'twocol-sideright (Two columns, right sidebar.)' => 'twocol-sideright',
			'full-page (Full page.)' => 'full-page',
		),
		'peruser' => true,
	),
	array(
		'name' => 'width',
		'cname' => 'Width',
		'description' => 'Width of the layout in pixels. Use 0 for fluid width.',
		'value' => 0,
		'peruser' => true,
	),
	array(
		'name' => 'use_header_image',
		'cname' => 'Use Header Image',
		'description' => 'Show a header image (instead of just text) at the top of the page.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'header_image',
		'cname' => 'Header Image',
		'description' => 'The header image to use.',
		'value' => (isset($_SESSION['user']->group) && is_callable(array($_SESSION['user']->group, 'get_logo'))) ? $_SESSION['user']->group->get_logo() : $pines->config->location.$pines->config->upload_location.'logos/default_logo.png',
		'peruser' => true,
	),
	array(
		'name' => 'fancy_style',
		'cname' => 'Fancy Styling',
		'description' => 'Use fancier styling modifications.',
		'value' => array('printfix', 'printheader', 'shadows'),
		'options' => array(
			'Hide non-content positions.' => 'printfix',
			'Show the page header when non-content positions are hidden.' => 'printheader',
			'Drop shadows.' => 'shadows',
			'No gutters on the sides.' => 'nosidegutters',
		),
		'peruser' => true,
	),
	array(
		'name' => 'buttonized_menu',
		'cname' => 'Buttonized Menu',
		'description' => 'Make the main menu look more like buttons.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'center_menu',
		'cname' => 'Centered Menu',
		'description' => 'Center the main menu.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'menu_delay',
		'cname' => 'Menu Delay',
		'description' => 'Make menus delay before closing when the mouse leaves them. This makes it easier to navigate menus, but the menus may become slower.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'ajax',
		'cname' => 'Use Ajax',
		'description' => 'Use the experimental AJAX code to load pages without refreshing.',
		'value' => false,
		'peruser' => true,
	),
);

?>