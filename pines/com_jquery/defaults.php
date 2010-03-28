<?php
/**
 * com_jquery' configuration.
 *
 * @package Pines
 * @subpackage com_jquery
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'theme',
		'cname' => 'Theme',
		'description' => 'jQuery UI theme to use.',
		'value' => 'smoothness',
		'options' => array(
			'Black Hole' => 'black-hole',
			'Dark Hive' => 'dark-hive',
			'Dot Luv' => 'dot-luv',
			'Redmond' => 'redmond',
			'Smoothness' => 'smoothness',
			'Start' => 'start',
			'UI Darkness' => 'ui-darkness',
			'UI Lightness' => 'ui-lightness'
		)
	),
);

?>