<?php
/**
 * com_example's modules.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'example' => array(
		'cname' => 'Example Module',
		'description' => 'This example module works as both a module and a widget.',
		'view' => 'modules/example',
		// Only work as a regular module and a dashboard widget.
		'type' => 'module widget',
		'widget' => array(
			'default' => false, // Set to true to show up in new dashboards.
			'depends' => array( // Only show up when these conditions are met.
				'ability' => 'com_example/content',
			),
		),
	),
	'example2' => array(
		'cname' => 'Foobar Module',
		'description' => 'This Foobar module will show a foobar\'s description.',
		'view' => 'modules/foobar',
		'form' => 'modules/foobar_form',
		// No type means it works as both regular and inline, but not a dashboard widget.
	),
	'iexample' => array(
		'cname' => 'Example Inline Module',
		'description' => 'This inline module creates a button.',
		'view' => 'modules/iexample',
		// Only work as an inline module.
		'type' => 'imodule',
	),
	'widget' => array(
		'cname' => 'Example Widget',
		'description' => 'This dashboard widget can be used to show a foobar\'s description.',
		'view' => 'modules/widget',
		'form' => 'modules/widget_form',
		// Only work as a dashboard widget.
		'type' => 'widget',
		'widget' => array(
			'default' => false,
			'depends' => array(
				'ability' => 'com_example/content',
			),
		),
	),
);

?>