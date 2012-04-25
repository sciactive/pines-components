<?php
/**
 * tpl_bamboo's information.
 *
 * @package Templates
 * @subpackage bamboo
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Bamboo Template',
	'author' => 'SciActive (Template), Free CSS Templates (Design)',
	'version' => '1.0.1dev',
	'license' => 'http://creativecommons.org/licenses/by/2.5/',
	'website' => 'http://www.freecsstemplates.org',
	'services' => array('template'),
	'positions' => array(
		'top',
		'header',
		'header_right',
		'content',
		'left',
		'right',
		'footer',
		'bottom'
	),
	'short_description' => 'A pretty bamboo style template',
	'description' => 'An pretty bamboo style template.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
	'recommend' => array(
		'component' => 'com_pnotify'
	),
);

?>