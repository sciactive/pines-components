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
		'view' => 'modules/example',
		// Only work as a regular module.
		'type' => 'module',
	),
	'example2' => array(
		'cname' => 'Widget Module',
		'view' => 'modules/widget',
		'form' => 'modules/widget_form',
		// No type means it works as both regular and inline.
	),
	'iexample' => array(
		'cname' => 'Example Inline Module',
		'view' => 'modules/iexample',
		// Only work as an inline module.
		'type' => 'imodule',
	),
);

?>