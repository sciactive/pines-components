<?php
/**
 * tpl_simple's information.
 *
 * @package Templates\bootstrap
 * @license http://opensource.org/licenses/MIT
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright http://www.verticolabs.com
 * @link http://www.verticolabs.com
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Simple Template',
	'author' => 'Angela Murrell',
	'version' => '1.0.0alpha1',
	'license' => 'http://opensource.org/licenses/MIT',
	'website' => 'http://www.verticolabs.com',
	'services' => array('template'),
	'positions' => array(
		'head',
		'top',
		'header',
		'header_right',
		'breadcrumbs',
		'pre_content',
		'left',
		'content_top_left',
		'content_top_right',
		'content',
		'content_bottom_left',
		'content_bottom_right',
		'right',
		'post_content',
		'footer',
		'bottom',
	),
	'short_description' => 'Bootstrap styled template with Compressed CSS,JS',
	'description' => 'A simplified Bootstrap Template that entirely focuses on reducing css and js requests in pines.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery&com_bootstrap'
	),
	'recommend' => array(
		'component' => 'com_pnotify'
	),
);

?>