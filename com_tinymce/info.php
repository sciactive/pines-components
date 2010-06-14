<?php
/**
 * com_tinymce's information.
 *
 * @package Pines
 * @subpackage com_tinymce
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'TinyMCE',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('editor'),
	'short_description' => 'TinyMCE editor widget',
	'description' => 'TinyMCE based editor widget.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
	'recommend' => array(
		'component' => 'com_elfinder'
	),
);

?>