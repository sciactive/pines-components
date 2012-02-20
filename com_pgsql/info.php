<?php
/**
 * com_pgsql's information.
 *
 * @package Pines
 * @subpackage com_pgsql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'PostgreSQL Link Manager',
	'author' => 'SciActive',
	'version' => '1.0.1dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'PostgreSQL link manager',
	'description' => 'Provides an easy way to manage links to one or more databases, and the ability to keep more than one data set in those databases.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'configurator',
		'component' => 'com_pform',
		'function' => 'pg_connect'
	),
);

?>