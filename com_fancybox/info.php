<?php
/**
 * com_fancybox's information.
 *
 * @package Components
 * @subpackage fancybox
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'fancyBox',
	'author' => 'SciActive (Component), Janis Skarnelis (JavaScript)',
	'version' => '2.0.6-2.0.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'fancyBox jQuery plugin.',
	'description' => 'A JavaScript lightbox alternative jQuery component.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
);

?>