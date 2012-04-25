<?php
/**
 * com_elfinderupload's information.
 *
 * @package Components\elfinderupload
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'elFinder Upload Widget',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('uploader'),
	'short_description' => 'elFinder file upload widget',
	'description' => 'A standard file upload widget using the elFinder jQuery plugin.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_elfinder&com_jquery&com_bootstrap&com_pform'
	),
);

?>