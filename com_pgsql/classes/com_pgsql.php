<?php
/**
 * com_pgsql class.
 *
 * @package Pines
 * @subpackage com_pgsql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_pgsql main class.
 *
 * Connect to and disconnect from a PostgreSQL database.
 *
 * @package Pines
 * @subpackage com_pgsql
 */
class com_pgsql extends component {
	/**
	 * Whether this instance is currently connected to a database.
	 *
	 * @var bool
	 */
	public $connected = false;
	/**
	 * The PostgreSQL link identifier for this instance.
	 *
	 * @var mixed
	 */
	public $link = null;

	/**
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param string $connection_type
	 * @uses com_pgsql::connect()
	 */
	public function __construct($host = null, $user = null, $password = null, $database = null, $connection_type = null) {
		$this->connect($host, $user, $password, $database, $connection_type);
	}

	/**
	 * Disconnect from the database on destruction.
	 */
	public function __destruct() {
		$this->disconnect();
	}

	/**
	 * Connect to a PostgreSQL database.
	 *
	 * If the host is not specified, all the parameters will be filled with
	 * their defaults. If the host is specified, none of the parameters are
	 * altered. This is a security feature, to keep a component from
	 * accidentally revealing your credentials to another host. It is still
	 * possible for a component to do this, but it most likely would not be
	 * accidental.
	 *
	 * @param string $host The host to connect to. Use a blank string if connecting via Unix socket.
	 * @param string $user The username to connect to.
	 * @param string $password The password to connect to.
	 * @param string $database The database to connect to.
	 * @param string $connection_type The connection type to attempt. (host or socket)
	 * @return bool Whether this instance is connected to a PostgreSQL database after the method has run.
	 */
	public function connect($host = null, $user = null, $password = null, $database = null, $connection_type = null) {
		global $pines;
		// Check that the PostgreSQL extension is installed.
		if (!is_callable('pg_connect')) {
			pines_error('PostgreSQL PHP extension is not available. It probably has not been installed. Please install and configure it in order to use PostgreSQL.');
			return false;
		}
		// If we're setting up the DB, don't try to connect.
		if ($pines->request_component == 'com_pgsql' && $pines->request_action == 'setup')
			return false;
		// If something changes the host, it could reveal the user and password.
		if (!isset($host)) {
			$host = $pines->config->com_pgsql->host;
			$connection_type = $pines->config->com_pgsql->connection_type;
			if (!isset($user)) $user = $pines->config->com_pgsql->user;
			if (!isset($password)) $password = $pines->config->com_pgsql->password;
			if (!isset($database)) $database = $pines->config->com_pgsql->database;
		}
		// Connecting, selecting database
		if (!$this->connected) {
			if ($connection_type == 'host') {
				$connect_string = 'host=\''.addslashes($host).'\' dbname=\''.addslashes($database).'\' user=\''.addslashes($user).'\' password=\''.addslashes($password).'\' connect_timeout=5';
			} else {
				$connect_string = 'dbname=\''.addslashes($database).'\' user=\''.addslashes($user).'\' password=\''.addslashes($password).'\' connect_timeout=5';
			}
			if ($pines->config->com_pgsql->allow_persistent) {
				$this->link = @pg_connect($connect_string);
			} else {
				$this->link = pg_connect($connect_string.' options=\''.addslashes(rand()).'\'', PGSQL_CONNECT_FORCE_NEW);
			}
			if ($this->link) {
				$this->connected = true;
				pines_notice(pg_connection_busy($this->link));
			} else {
				$this->connected = false;
				if (!isset($_SESSION['user']) && $host == 'localhost' && $user == 'pines' && $password == 'password' && $database == 'pines' && $connection_type == 'host') {
					if ($pines->request_component != 'com_pgsql')
						redirect(pines_url('com_pgsql', 'setup'));
				} else {
					if (function_exists('pines_error'))
						@pines_error('Could not connect: ' . pg_last_error());
				}
			}
		}
		return $this->connected;
	}

	/**
	 * Disconnect from a PostgreSQL database.
	 *
	 * @return bool Whether this instance is connected to a PostgreSQL database after the method has run.
	 */
	public function disconnect() {
		if ($this->connected) {
			pg_close($this->link);
			$this->connected = false;
		}
		return $this->connected;
	}
}

?>