<?php
/**
 * com_pgsql's configuration defaults.
 *
 * @package Pines
 * @subpackage com_pgsql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'host',
		'cname' => 'Host',
		'description' => 'The default PostgreSQL host.',
		'value' => 'localhost',
	),
	array(
		'name' => 'user',
		'cname' => 'User',
		'description' => 'The default PostgreSQL user.',
		'value' => 'pines',
	),
	array(
		'name' => 'password',
		'cname' => 'Password',
		'description' => 'The default PostgreSQL password.',
		'value' => 'password',
	),
	array(
		'name' => 'database',
		'cname' => 'Database',
		'description' => 'The default PostgreSQL database.',
		'value' => 'pines',
	),
	array(
		'name' => 'prefix',
		'cname' => 'Table Prefix',
		'description' => 'The default PostgreSQL table name prefix.',
		'value' => 'pin_',
	),
);

?>