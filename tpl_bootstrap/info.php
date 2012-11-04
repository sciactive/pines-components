<?php
/**
 * tpl_bootstrap's information.
 *
 * @package Templates\bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Bootstrap Template',
	'author' => 'SciActive',
	'version' => '1.0.0alpha1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
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
	'short_description' => 'Bootstrap styled template',
	'description' => 'A well integrated template, completely styled with Twitter Bootstrap.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery&com_bootstrap'
	),
	'recommend' => array(
		'component' => 'com_pnotify'
	),
);

?>