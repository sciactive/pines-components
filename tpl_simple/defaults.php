<?php
/**
 * tpl_spacepak's configuration.
 *
 * @package Templates\spacepak
 * @license Proprietary
 * @author Angela Murrell <angela@verticolabs.com>
 * @copyright verticolabs.com
 * @link http://verticolabs.com
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
			'full-fluid-page (Full Fluid page.)' => 'full-fluid-page',
		),
		'peruser' => true,
	),
	array(
		'name' => 'width',
		'cname' => 'Width',
		'description' => 'Fluid or fixed width.',
		'value' => 'fixed',
		'options' => array(
			'Fluid Width' => 'fluid',
			'Fixed Width' => 'fixed',
		),
		'peruser' => true,
	),
        array(
		'name' => 'favicon_url',
		'cname' => 'Favicon Url',
		'description' => 'The url of the favicon.',
		'value' => $pines->config->location.'favicon.ico',
                'peruser' => true,
        ),
        array(
		'name' => 'brand_type',
		'cname' => 'Textual Brand Type',
		'description' => 'Choose which textual form to use as the brand in the navbar.',
		'value' => 'System Name',
		'options' => array(
			'System Name',
			'Page Title',
			'Custom',
		),
		'peruser' => true,
	),
        array(
		'name' => 'brand_name',
		'cname' => 'Custom Brand Name',
		'description' => 'Specify a custom brand Name to appear as text.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'use_header_image',
		'cname' => 'Use Header Image',
		'description' => 'Show a header image (instead of just text) at the top of the page.',
		'value' => false,
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
		'name' => 'show_navigation',
		'cname' => 'Show navbar.',
		'description' => 'Show the Main Navbar',
		'value' => true,
		'peruser' => true,
	),
    array(
		'name' => 'navbar_fixed',
		'cname' => 'Navbar Fixed to Top',
		'description' => 'Fix the navbar to the top of the page.',
		'value' => true,
		'peruser' => true,
	),
    array(
		'name' => 'navbar_trigger',
		'cname' => 'Navbar Trigger',
		'description' => 'Trigger menu items with click or hover.',
		'value' => 'hover',
		'options' => array(
			'Click' => 'click',
			'Hover' => 'hover',
		),
		'peruser' => true,
	),
    array(
		'name' => 'mobile_menu',
		'cname' => 'Mobile Menu',
		'description' => 'Use the mobile menu from bootstrap or an adjusted one.',
		'value' => 'adjusted',
		'options' => array(
			'Use Adjusted' => 'adjusted',
			'Use Bootstrap' => 'bootstrap',
		),
		'peruser' => true,
	),
	array(
		'name' => 'footer_height',
		'cname' => 'Footer Height',
		'description' => 'Use the default template\'s footer or an adjusted one with jquery. The jquery one will extend to the rest of the page (noticeable on short pages).',
		'value' => 'adjusted',
		'options' => array(
			'Use Adjusted' => 'adjusted',
			'Use Default' => 'default',
		),
		'peruser' => true,
	),
	array(
		'name' => 'footer_type',
		'cname' => 'Footer Type',
		'description' => 'Fixed so that the footer is always at the bottom of the window, or always at the bottom of the page.',
		'value' => 'default',
		'options' => array(
			'Fixed to Bottom of the Window' => 'fixed',
			'Bottom of Page' => 'default',
		),
		'peruser' => true,
	),
	array(
		'name' => 'font_folder',
		'cname' => 'Font Face Folder',
		'description' => 'This is the folder where fontface kits should be installed, with the following file types: eot, svg, ttf, and woff. There should be one css file that creates the font-family rule. You have to put these into your media folder manually, as the file manager will not allow these file types. EX. /media/fonts/',
		'value' => '',
		'peruser' => true,
	),
    array(
		'name' => 'override_pgrid_aristo',
		'cname' => 'Use Aristo Pgrid Override Styles.',
		'description' => 'Use on light template designs and with jquery theme aristo. Makes the grid much more readable and modern looking.',
		'value' => true,
		'peruser' => true,
	),
    array(
		'name' => 'link_css',
		'cname' => 'Link Pines Relative CSS List',
		'description' => 'Comma Separate a list of urls to link to the template. NOT loaded with JavaScript. Write the file starting with components/component_name/includes/...',
		'value' => '',
		'peruser' => true,
	),
    array(
		'name' => 'load_js',
		'cname' => 'Load Pines Relative JS List',
		'description' => 'Comma Separate a list of urls to pines load in the template. Loaded WITH JavaScript. Write the url starting with components/component_name/includes/... or media/website/js/...',
		'value' => '',
		'peruser' => true,
	),
);

?>