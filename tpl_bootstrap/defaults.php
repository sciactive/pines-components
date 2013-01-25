<?php
/**
 * tpl_bootstrap's configuration.
 *
 * @package Templates\bootstrap
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
		'description' => 'Fluid or fixed width.',
		'value' => 'fixed',
		'options' => array(
			'Fluid Width' => 'fluid',
			'Fixed Width' => 'fixed',
		),
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
		'name' => 'alt_navbar',
		'cname' => 'Alternate Navbar',
		'description' => 'Use the Bootstrap theme\'s alternate navbar styling.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'fancy_style',
		'cname' => 'Fancy Styling',
		'description' => 'Use fancier styling modifications.',
		'value' => array('printfix', 'printheader'),
		'options' => array(
			'Hide non-content positions.' => 'printfix',
			'Show the page header when non-content positions are hidden.' => 'printheader',
			'No gutters on the sides.' => 'nosidegutters',
		),
		'peruser' => true,
	),
	array(
		'name' => 'ajax',
		'cname' => 'Use Ajax',
		'description' => 'Use the experimental AJAX code to load pages without refreshing.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'darker_color',
		'cname' => 'Darker Navbar Color',
		'description' => 'This color will be the bottom color in the gradient of the navbar. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'lighter_color',
		'cname' => 'Lighter Navbar Color',
		'description' => 'This color will be the top color in the gradient of the navbar. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'border_color',
		'cname' => 'Border Navbar Color',
		'description' => 'This color will be the border color of the navbar. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'font_color',
		'cname' => 'Font Navbar Color',
		'description' => 'This color will be the font color of the navbar. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'font_hover_color',
		'cname' => 'Font Navbar Hover Color',
		'description' => 'This color will be the font color of the navbar when hovered. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'caret_color',
		'cname' => 'Caret Navbar Color',
		'description' => 'This color will be the caret color for each top menu link on the navbar. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'caret_hover_color',
		'cname' => 'Caret Navbar Hover Color',
		'description' => 'This color will be the caret color for each top menu link on the navbar when hovered. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'brand_color',
		'cname' => 'Brand Navbar Color',
		'description' => 'This color will be the brand color on the navbar. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'brand_hover_color',
		'cname' => 'Brand Navbar Hover Color',
		'description' => 'This color will be the brand color on the navbar when hovered. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'navbar_menu_height',
		'cname' => 'Navbar Menu Height',
		'description' => 'Adjust the menu height with this option. Enter in the number of pixels to set height.',
		'value' => "",
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
		'name' => 'footer_background',
		'cname' => 'Footer Background',
		'description' => 'This color will be the background color of the footer. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'footer_border',
		'cname' => 'Footer Border',
		'description' => 'This color will be the border color of the footer. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
		'peruser' => true,
	),
	array(
		'name' => 'footer_font_color',
		'cname' => 'Footer font Color',
		'description' => 'This color will be the font color of the footer. You can use hex colors - #000000 - or rgb/rgba - rgb(255,255,255) - or hsl/hsla - hsl(255,100%,50%)',
		'value' => "",
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
		'name' => 'brand_fontface',
		'cname' => 'Brand Font Face',
		'description' => 'This is the font from your fonts folder you would like to use for your brand. Make sure to spell it exactly how it is in the css in your fonts folder. You can also put default fonts in here.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'menu_fontface',
		'cname' => 'Menu Font Face',
		'description' => 'This is the font from your fonts folder you would like to use for your menu. Make sure to spell it exactly how it is in the css in your fonts folder. You can also put default fonts in here.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'headings_fontface',
		'cname' => 'Headings Font Face',
		'description' => 'This is the font from your fonts folder you would like to use for your headings. Make sure to spell it exactly how it is in the css in your fonts folder. You can also put default fonts in here.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'body_fontface',
		'cname' => 'Body Font Face',
		'description' => 'This is the font from your fonts folder you would like to use for your body. Make sure to spell it exactly how it is in the css in your fonts folder. You can also put default fonts in here.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'menu_css',
		'cname' => 'Menu Links Custom CSS',
		'description' => 'This is menu css you want to use to control the links. EX text-transform: uppercase; font-size:1.1em',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'brand_css',
		'cname' => 'Brand Link Custom CSS',
		'description' => 'This is menu css you want to use to control the links. EX text-transform: uppercase; font-size:1.1em',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'headings_css',
		'cname' => 'Headings Custom CSS',
		'description' => 'This is menu css you want to use to control the links. EX text-transform: uppercase; font-size:1.1em',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'body_css',
		'cname' => 'Body Custom CSS',
		'description' => 'With this option, you can make changes to the font, background (background images), margins etc. EX color: #333333; font-size:1.1em; background: #FFFFFF',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'nav_list_css',
		'cname' => 'Navigation List Custom CSS - Not Mobile',
		'description' => 'With this option, you can make changes to the entire menu list (not mobile), like put a line under it. EX border-bottom: 1px solid #000000',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'nav_bar_css',
		'cname' => 'Navigation Bar Custom CSS',
		'description' => 'With this option, you can make changes to the entire menu bar, like use a background image and remove the bottom border, and maybe add a box shadow. EX background-image: url() repeat',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'footer_css',
		'cname' => 'Footer Custom CSS',
		'description' => 'With this option, you can make changes to the entire Footer, like use a background image and remove the top border, and maybe add a box shadow. EX background-image: url() repeat',
		'value' => '',
		'peruser' => true,
	),
);

?>