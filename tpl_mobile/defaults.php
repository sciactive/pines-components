<?php
/**
 * tpl_mobile's configuration.
 *
 * @package Pines
 * @subpackage tpl_mobile
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
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
		'value' => 'full',
		'options' => array(
			'full (Show content, left, and right modules.)' => 'full',
			'left (Show content and left modules.)' => 'left',
			'right (Show content and right modules.)' => 'right',
			'content (Show content modules.)' => 'content',
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
		'description' => 'The header image to use. Should be 40 pixels tall.',
		'value' => $pines->config->rela_location.'templates/tpl_mobile/images/default_logo.png',
		'peruser' => true,
	),
);

?>