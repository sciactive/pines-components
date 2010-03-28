<?php
/**
 * com_mysql's configuration defaults.
 *
 * @package Pines
 * @subpackage com_mysql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'host',
		'cname' => 'Host',
		'description' => 'The default MySQL host.',
		'value' => 'localhost',
	),
	array(
		'name' => 'user',
		'cname' => 'User',
		'description' => 'The default MySQL user.',
		'value' => 'pines',
	),
	array(
		'name' => 'password',
		'cname' => 'Password',
		'description' => 'The default MySQL password.',
		'value' => 'password',
	),
	array(
		'name' => 'database',
		'cname' => 'Database',
		'description' => 'The default MySQL database.',
		'value' => 'pines',
	),
	array(
		'name' => 'prefix',
		'cname' => 'Table Prefix',
		'description' => 'The default MySQL table name prefix.',
		'value' => 'pin_',
	),
);

?>