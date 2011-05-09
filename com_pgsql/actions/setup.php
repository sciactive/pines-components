<?php
/**
 * Setup the database.
 *
 * @package Pines
 * @subpackage com_pgsql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_SESSION['user']) || $pines->config->com_pgsql->host != 'localhost' || $pines->config->com_pgsql->user != 'pines' || $pines->config->com_pgsql->password != 'password' || $pines->config->com_pgsql->database != 'pines' || $pines->config->com_pgsql->prefix != 'pin_')
	return;

// Get the provided or default info.
$host = isset($_REQUEST['host']) ? $_REQUEST['host'] : $pines->config->com_pgsql->host;
$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : $pines->config->com_pgsql->user;
$password = $_REQUEST['password'];
$database = isset($_REQUEST['database']) ? $_REQUEST['database'] : $pines->config->com_pgsql->database;
$prefix = isset($_REQUEST['prefix']) ? $_REQUEST['prefix'] : $pines->config->com_pgsql->prefix;
$setup_user = $_REQUEST['setup_user'];
$setup_password = $_REQUEST['setup_password'];

if (isset($_REQUEST['host'])) {
	// The user already filled out the form.
	$pass = true;
	if (!empty($_REQUEST['setup_user'])) {
		// Can the user connect already?
		$can_connect = @pg_connect('host=\''.addslashes($host).'\' user=\''.addslashes($user).'\' password=\''.addslashes($password).'\'');
		if ($can_connect)
			@pg_close($can_connect);
		if ($link = @pg_connect('host=\''.addslashes($host).'\' user=\''.addslashes($setup_user).'\' password=\''.addslashes($setup_password).'\'')) {
			// Create the user/database.
			if ($pass && !$can_connect) {
				// Create the user.
				$pass = $pass && @pg_query($link, 'DROP ROLE IF EXISTS "'.pg_escape_string($link, $user).'";');
				$pass = $pass && @pg_query($link, 'CREATE ROLE "'.pg_escape_string($link, $user).'" INHERIT LOGIN PASSWORD \''.pg_escape_string($link, $password).'\' VALID UNTIL \'infinity\';');
				$pass = $pass && @pg_query($link, 'COMMENT ON ROLE "'.pg_escape_string($link, $user).'" IS \'Automatically created by com_pgsql for Pines.\';');
			}
			// Create the database.
			if ($pass)
				$pass = $pass && @pg_query($link, 'CREATE DATABASE "'.pg_escape_string($link, $database).'" WITH OWNER = "'.pg_escape_string($link, $user).'" ENCODING = \'UTF8\' TABLESPACE = pg_default LC_COLLATE = \'en_US.utf8\' LC_CTYPE = \'en_US.utf8\' CONNECTION LIMIT = -1;');
			// Grant priveleges to use it.
			if ($pass)
				$pass = $pass && @pg_query($link, 'GRANT ALL ON DATABASE "'.pg_escape_string($link, $database).'" TO "'.pg_escape_string($link, $user).'";');
			if (!$pass) {
				pines_error('User/database could not be created. Check the log for more details.');
				pines_log('User/database could not be created: '.pg_last_error(), 'error');
			}
			@pg_close($link);
		} else {
			$pass = false;
			pines_error('Can\'t connect to host using setup user: '.pg_last_error());
		}
	}
	if ($pass) {
		// Can the user connect?
		$can_connect = @pg_connect('host=\''.addslashes($host).'\' dbname=\''.addslashes($database).'\' user=\''.addslashes($user).'\' password=\''.addslashes($password).'\'');
		if ($can_connect) {
			// User can select the DB, so save the config.
			$conf = configurator_component::factory('com_pgsql');
			$conf->set_config(array(
				'host' => $host,
				'user' => $user,
				'password' => $password,
				'database' => $database,
				'prefix' => $prefix,
			));
			$conf->save_config();
			redirect(pines_url());
			@mysql_close($can_connect);
		} else {
			pines_error('Can\'t connect to host: '.pg_last_error());
		}
	}
}

// Print out the setup form.
$module = new module('com_pgsql', 'setup', 'content');
$module->host = $host;
$module->user = $user;
$module->password = $password;
$module->database = $database;
$module->prefix = $prefix;
$module->setup_user = $setup_user;
$module->setup_password = $setup_password;

?>