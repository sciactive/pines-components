<?php
/**
 * com_tinymce's information.
 *
 * @package Components\tinymce
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'TinyMCE',
	'author' => 'SciActive',
	'version' => '3.5.3-1.0.1beta3',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('editor'),
	'short_description' => 'TinyMCE editor widget',
	'description' => 'TinyMCE based editor widget.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery&com_pform'
	),
	'recommend' => array(
		'component' => 'com_elfinder'
	),
);

?>