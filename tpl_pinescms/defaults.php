<?php
/**
 * tpl_pinescms' configuration.
 *
 * @package Pines
 * @subpackage tpl_pinescms
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
		'value' => 'fluid-sideright',
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
);

?>