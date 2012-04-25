<?php
/**
 * tpl_pinescms' configuration.
 *
 * @package Templates\pinescms
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'variant',
		'cname' => 'Page Variant/Layout',
		'description' => 'The layout of the page.',
		'value' => 'fixed-sideright',
		'options' => array(
			'fixed-sideleft (Fixed width, left sidebar.)' => 'fixed-sideleft',
			'fixed-sideright (Fixed width, right sidebar.)' => 'fixed-sideright',
			'fixed-full (Fixed width, no sidebar.)' => 'fixed-full',
			'fluid-sideleft (Fluid width, left sidebar.)' => 'fluid-sideleft',
			'fluid-sideright (Fluid width, right sidebar.)' => 'fluid-sideright',
			'fluid-full (Fluid width, no sidebar.)' => 'fluid-full',
		),
		'peruser' => true,
	),
	array(
		'name' => 'display_header',
		'cname' => 'Display Header',
		'description' => 'Display the top page header. (May be reduntant if using navigation logo.)',
		'value' => true,
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
		'value' => $pines->config->rela_location.'templates/tpl_pinescms/images/default_logo.png',
		'peruser' => true,
	),
	array(
		'name' => 'navigation_orientation',
		'cname' => 'Navigation Orientation',
		'description' => 'Navigation to the left or the right.',
		'value' => 'nav-right',
		'options' => array(
			'left (Navigation left justified)' => 'nav-left',
			'right (Navigation right justified.)' => 'nav-right',
		),
		'peruser' => true,
	),
	array(
		'name' => 'navigation_fixed',
		'cname' => 'Navigation Fixed',
		'description' => 'Navigation Bar is Fixed to the top.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'use_nav_logo',
		'cname' => 'Use Nav Logo',
		'description' => 'Use a logo in the navigation bar.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'nav_logo_image',
		'cname' => 'Nav Logo Image',
		'description' => 'The navigation logo image to use.',
		'value' => $pines->config->rela_location.'templates/tpl_pinescms/images/default_nav_logo.png',
		'peruser' => true,
	),
	array(
		'name' => 'show_recycled_bits',
		'cname' => 'Show Recycled Bits',
		'description' => 'Show recycled bits in footer.',
		'value' => true,
		'peruser' => true,
	),
);

?>