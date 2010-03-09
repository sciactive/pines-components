<?php
/**
 * tpl_pines' configuration.
 *
 * @package Pines
 * @subpackage com_jquery
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
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
			'Dark Hive' => 'dark-hive',
			'Redmond' => 'redmond',
			'Smoothness' => 'smoothness',
			'Start' => 'start',
			'UI Darkness' => 'ui-darkness',
			'UI Lightness' => 'ui-lightness'
		)
	),
);

?>