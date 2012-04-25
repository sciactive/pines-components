<?php
/**
 * Setup the database.
 *
 * @package Components
 * @subpackage mysql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_SESSION['user']) || $pines->config->com_mysql->host != 'localhost' || $pines->config->com_mysql->user != 'pines' || $pines->config->com_mysql->password != 'password' || $pines->config->com_mysql->database != 'pines' || $pines->config->com_mysql->prefix != 'pin_')
	return;

// Get the provided or default info.
$host = isset($_REQUEST['host']) ? $_REQUEST['host'] : $pines->config->com_mysql->host;
$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : $pines->config->com_mysql->user;
$password = $_REQUEST['password'];
$database = isset($_REQUEST['database']) ? $_REQUEST['database'] : $pines->config->com_mysql->database;
$prefix = isset($_REQUEST['prefix']) ? $_REQUEST['prefix'] : $pines->config->com_mysql->prefix;
$setup_user = $_REQUEST['setup_user'];
$setup_password = $_REQUEST['setup_password'];

if (isset($_REQUEST['host'])) {
	// The user already filled out the form.
	$pass = true;
	if (!empty($_REQUEST['setup_user'])) {
		// Can the user connect already?
		$can_connect = @mysql_connect($host, $user, $password);
		if ($can_connect)
			@mysql_close($can_connect);
		if ($link = @mysql_connect($host, $setup_user, $setup_password)) {
			// Create the user/database.
			$pass = $pass && @mysql_query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";', $link);
			// Find out our hostname.
			$resource = @mysql_query('SELECT USER();');
			$my_host = mysql_fetch_row($resource);
			mysql_free_result($resource);
			$my_host = mysql_real_escape_string(preg_replace('/.*@/', '', $my_host[0]), $link);
			if ($pass && !$can_connect) {
				// Create the user.
				$pass = $pass && @mysql_query('CREATE USER \''.mysql_real_escape_string($user, $link).'\'@\''.$my_host.'\' IDENTIFIED BY \''.mysql_real_escape_string($password, $link).'\';', $link);
				if ($pass)
					$pass = $pass && @mysql_query('GRANT USAGE ON *.* TO \''.mysql_real_escape_string($user, $link).'\'@\''.$my_host.'\' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;', $link);
			}
			// Create the database.
			if ($pass)
				$pass = $pass && @mysql_query('CREATE DATABASE IF NOT EXISTS `'.mysql_real_escape_string($database, $link).'`;', $link);
			// Grant priveleges to use it.
			if ($pass)
				$pass = $pass && @mysql_query('GRANT ALL PRIVILEGES ON `'.mysql_real_escape_string($database, $link).'`.* TO \''.mysql_real_escape_string($user, $link).'\'@\''.$my_host.'\';', $link);
			if (!$pass)
				pines_error('User/database could not be created.');
			@mysql_close($link);
		} else {
			$pass = false;
			pines_error('Can\'t connect to host using setup user: '.mysql_error());
		}
	}
	if ($pass) {
		// Can the user connect?
		$can_connect = @mysql_connect($host, $user, $password);
		if ($can_connect) {
			if (@mysql_select_db($database, $can_connect)) {
				// User can select the DB, so save the config.
				$conf = configurator_component::factory('com_mysql');
				$conf->set_config(array(
					'host' => $host,
					'user' => $user,
					'password' => $password,
					'database' => $database,
					'prefix' => $prefix,
				));
				$conf->save_config();
				pines_redirect(pines_url());
			} else {
				pines_error('Can\'t select database: '.mysql_error());
			}
			@mysql_close($can_connect);
		} else {
			pines_error('Can\'t connect to host: '.mysql_error());
		}
	}
}

// Print out the setup form.
$module = new module('com_mysql', 'setup', 'content');
$module->host = $host;
$module->user = $user;
$module->password = $password;
$module->database = $database;
$module->prefix = $prefix;
$module->setup_user = $setup_user;
$module->setup_password = $setup_password;

?>