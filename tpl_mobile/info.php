<?php
/**
 * tpl_mobile's information.
 *
 * @package Pines
 * @subpackage tpl_mobile
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Mobile Pines Template',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('template'),
	'positions' => array(
		'top',
		'header',
		'header_right',
		'pre_content',
		'content_top_left',
		'content_top_right',
		'content',
		'content_bottom_left',
		'content_bottom_right',
		'left',
		'right',
		'post_content',
		'footer',
		'bottom'
	),
	'short_description' => 'jQuery UI styled template for mobile browsers',
	'description' => 'A template optimized for mobile browsers, completely styled with jQuery UI.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery&com_bootstrap'
	),
	'recommend' => array(
		'component' => 'com_pnotify&com_uasniffer'
	),
);

?>