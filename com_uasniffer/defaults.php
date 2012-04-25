<?php
/**
 * com_uasniffer's configuration defaults.
 *
 * @package Components
 * @subpackage uasniffer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'mobile_site',
		'cname' => 'Mobile Site',
		'description' => 'Provide a different template to user\'s who appear to be using a mobile browser.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'mobile_template',
		'cname' => 'Mobile Template',
		'description' => 'The template to use if a mobile browser is detected.',
		'value' => 'tpl_mobile',
		'options' => pines_scandir('templates/'),
		'peruser' => true,
	),
	array(
		'name' => 'switcher',
		'cname' => 'Show Switcher on Mobiles',
		'description' => 'Show a switcher to go back to the desktop version when the mobile version is used.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'switcher_pos',
		'cname' => 'Switcher Position',
		'description' => 'The position on the page to place the switcher. (If you need more customization, you can use com_modules and the "browser" condition type with "mobile" to add a customized switcher module.)',
		'value' => 'bottom',
		'peruser' => true,
	),
	array(
		'name' => 'use_simple_mobile_detection',
		'cname' => 'Use a Simpler Mobile Detection',
		'description' => 'Use a simpler way of detecting mobile browsers. This method won\'t detect some more obscure mobile browsers, but may be less likely to give false positives.',
		'value' => false,
		'peruser' => true,
	),
);

?>