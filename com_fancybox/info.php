<?php
/**
 * com_fancybox's information.
 *
 * @package Pines
 * @subpackage com_fancybox
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'jQuery.fancybox',
	'author' => 'SciActive (Component), Janis Skarnelis (JavaScript)',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'jQuery.fancybox jQuery plugin',
	'description' => 'A JavaScript image gallery jQuery component.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
);

?>