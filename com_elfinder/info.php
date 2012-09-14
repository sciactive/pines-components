<?php
/**
 * com_elfinder's information.
 *
 * @package Components\elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'elFinder File Manager',
	'author' => 'SciActive (Component), Studio 42 Ltd. (elFinder JS and PHP)',
	'version' => '2.0rc1-1.1.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'elFinder file manager and widget',
	'description' => 'A file manager using the elFinder jQuery plugin. See the readme in the includes folder for elFinder\'s license information.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery'
	),
	'abilities' => array(
		array('finder', 'Use elFinder for All Files', 'User can manage all files with elFinder.'),
		array('finderself', 'Use elFinder for Own Files', 'User can manage files in their own folder with elFinder. Their folder is created under user root as their GUID.'),
	),
);

?>