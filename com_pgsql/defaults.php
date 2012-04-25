<?php
/**
 * com_pgsql's configuration defaults.
 *
 * @package Components\pgsql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'connection_type',
		'cname' => 'Connection Type',
		'description' => 'The type of connection to establish with PostreSQL. Choosing socket will attempt to use the default socket path. You can also choose host and provide the socket path as the host. If you get errors that it can\'t connect, check that your pg_hba.conf file allows the specified user to access the database through a socket.',
		'value' => 'host',
		'options' => array(
			'Host' => 'host',
			'Unix Socket' => 'socket',
		),
	),
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
	array(
		'name' => 'allow_persistent',
		'cname' => 'Allow Persistent Connections',
		'description' => 'Allow connections to persist, if that is how PHP is configured.',
		'value' => true,
	),
);

?>