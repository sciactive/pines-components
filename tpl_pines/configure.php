<?php
/**
 * tpl_pines' configuration.
 *
 * @package Pines
 * @subpackage tpl_pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 =>
  array (
	'name' => 'header_image',
	'cname' => 'Use Header Image',
	'description' => 'Whether to show a header image (instead of just text) at the top of the page.',
	'value' => true,
  ),
  1 =>
  array (
	'name' => 'theme',
	'cname' => 'Theme',
	'description' => 'jQuery UI theme to use.',
	'value' => 'smoothness',
	'options' => array(
		'Dark Hive' => 'dark-hive',
		'Redmond' => 'redmond',
		'Smoothness' => 'smoothness',
		'Start' => 'start',
		'UI Darkness' => 'ui-darkness',
		'UI Lightness' => 'ui-lightness'
	)
  ),
  2 =>
  array (
	'name' => 'theme_switcher',
	'cname' => 'Theme Switcher',
	'description' => 'Provide a theme switcher widget in the corner to choose a jQuery UI theme.',
	'value' => true,
  ),
  3 =>
  array (
	'name' => 'google_cdn',
	'cname' => 'Use Google\'s CDN',
	'description' => 'Use Google\'s content delivery network to host jQuery and jQuery UI.',
	'value' => true,
  ),
);

?>