<?php
/**
 * tpl_joomlatemplates' information.
 *
 * @package Templates
 * @subpackage joomlatemplates
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Joomla Template Adapter',
	'author' => 'SciActive',
	'version' => '0.0.1alpha',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('template'),
	'positions' => array(
		'top',
		'header',
		'header_right',
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
		'bottom'
	),
	'short_description' => 'Joomla! template adapter',
	'description' => 'An adapter that allows Joomla! templates to be used in Pines.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
	'recommend' => array(
		'component' => 'com_pnotify'
	),
);

?>