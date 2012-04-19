<?php
/**
 * com_pnotify's information.
 *
 * @package Pines
 * @subpackage com_pnotify
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Pines Notify',
	'author' => 'SciActive',
	'version' => '1.1.1-1.2.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Pines Notify jQuery plugin',
	'description' => 'A JavaScript notification jQuery component. Supports many features, and fully themeable using jQuery UI.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'icons',
		'component' => 'com_jquery'
	),
);

?>