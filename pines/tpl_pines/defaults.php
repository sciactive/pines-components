<?php
/**
 * tpl_pines' configuration.
 *
 * @package Pines
 * @subpackage tpl_pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'header_image',
		'cname' => 'Use Header Image',
		'description' => 'Whether to show a header image (instead of just text) at the top of the page.',
		'value' => true,
	),
	array(
		'name' => 'theme_switcher',
		'cname' => 'Theme Switcher',
		'description' => 'Provide a theme switcher widget in the corner to choose a jQuery UI theme.',
		'value' => true,
	),
);

?>