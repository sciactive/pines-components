<?php
/**
 * tpl_pinescms' information.
 *
 * @package Pines
 * @subpackage tpl_pinescms
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Pines CMS Template',
	'author' => 'SciActive',
	'version' => '1.0.1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('template'),
	'positions' => array(
		'top',
		'header',
		'header_right',
		'pre_content',
		'content',
		'left',
		'right',
		'post_content',
		'footer',
		'bottom'
	),
	'short_description' => 'PinesCMS.org and PinesFramework.org template',
	'description' => 'The template used on pinescms.org and pinesframework.org.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
	'recommend' => array(
		'component' => 'com_pnotify'
	),
);

?>