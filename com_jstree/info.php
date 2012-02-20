<?php
/**
 * com_jstree' information.
 *
 * @package Pines
 * @subpackage com_jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'jsTree',
	'author' => 'SciActive (Component), Ivan Bozhanov (JavaScript)',
	'version' => '1.0.1dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'jsTree jQuery plugin',
	'description' => 'A JavaScript tree jQuery component. Includes the context menu plugin.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
);

?>