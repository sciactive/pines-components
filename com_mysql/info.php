<?php
/**
 * com_mysql's information.
 *
 * @package Components\mysql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'MySQL Link Manager',
	'author' => 'SciActive',
	'version' => '1.0.1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'MySQL link manager',
	'description' => 'Provides an easy way to manage links to one or more databases, and the ability to keep more than one data set in those databases.',
	'depend' => array(
		'pines' => '<3',
		'service' => 'configurator',
		'component' => 'com_pform',
		'function' => 'mysql_connect'
	),
);

?>