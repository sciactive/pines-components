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
	'version' => '2.0.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('template'),
	'positions' => array(
		'top',
		'search',
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
	'short_description' => 'A nice default template for websites.',
	'description' => 'This is the template used on pinescms.org and pinesframework.org. It\'s a good template for websites, and is a good starting point for other templates.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery&com_bootstrap'
	),
	'recommend' => array(
		'component' => 'com_pnotify'
	),
);

?>