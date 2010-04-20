<?php
/**
 * tpl_pines' configuration.
 *
 * @package Pines
 * @subpackage tpl_pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'use_header_image',
		'cname' => 'Use Header Image',
		'description' => 'Whether to show a header image (instead of just text) at the top of the page.',
		'value' => true,
	),
	array(
		'name' => 'header_image',
		'cname' => 'Header Image',
		'description' => 'The header image to use.',
		'value' => $pines->config->rela_location.$pines->config->setting_upload.'logos/default_logo.png',
	),
);

?>