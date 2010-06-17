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
		'peruser' => true,
	),
	array(
		'name' => 'header_image',
		'cname' => 'Header Image',
		'description' => 'The header image to use.',
		'value' => isset($_SESSION['user']->group) ? $_SESSION['user']->group->get_logo() : $pines->config->rela_location.$pines->config->upload_location.'logos/default_logo.png',
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
		'name' => 'ajax',
		'cname' => 'Use Ajax',
		'description' => 'Use the experimental AJAX code to load pages without refreshing.',
		'value' => false,
		'peruser' => true,
	),
);

?>