<?php
/**
 * com_mysql class.
 *
 * @package Pines
 * @subpackage com_mysql
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_mysql main class.
 *
 * Connect to and disconnect from a MySQL database.
 *
 * @package Pines
 * @subpackage com_mysql
 */
class com_mysql extends component {
	/**
	 * Whether this instance is currently connected to a database.
	 *
	 * @var bool
	 */
	public $connected = false;
	/**
	 * The MySQL link identifier for this instance.
	 *
	 * @var mixed
	 */
	public $link = null;

	/**
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @uses com_mysql::connect()
	 */
	public function __construct($host = null, $user = null, $password = null, $database = null) {
		$this->connect($host, $user, $password, $database);
	}

	/**
	 * Disconnect from the database on destruction.
	 */
	public function __destruct() {
		$this->disconnect();
	}

	/**
	 * Connect to a MySQL database.
	 *
	 * If the host is not specified, all the parameters will be filled with
	 * their defaults. If the host is specified, none of the parameters are
	 * altered. This is a security feature, to keep a component from
	 * accidentally revealing your credentials to another host. It is still
	 * possible for a component to do this, but it most likely would not be
	 * accidental.
	 *
	 * @param string $host The host to connect to.
	 * @param string $user The username to connect to.
	 * @param string $password The password to connect to.
	 * @param string $database The database to connect to.
	 * @return bool Whether this instance is connected to a MySQL database after the method has run.
	 */
	public function connect($host = null, $user = null, $password = null, $database = null) {
		global $pines;
		// Check that the MySQL extension is installed.
		if (!is_callable('mysql_connect')) {
			pines_error('MySQL PHP extension is not available. It probably has not been installed. Please install and configure it in order to use MySQL.');
			return false;
		}
		// If we're setting up the DB, don't try to connect.
		if ($pines->request_component == 'com_mysql' && $pines->request_action == 'setup')
			return false;
		// If something changes the host, it could reveal the user and password.
		if (!isset($host)) {
			$host = $pines->config->com_mysql->host;
			if (!isset($user)) $user = $pines->config->com_mysql->user;
			if (!isset($password)) $password = $pines->config->com_mysql->password;
			if (!isset($database)) $database = $pines->config->com_mysql->database;
		}
		// Connecting, selecting database
		if (!$this->connected) {
			if ( $this->link = @mysql_connect($host, $user, $password) ) {
				if ( @mysql_select_db($database, $this->link) ) {
					$this->connected = true;
				} else {
					$this->connected = false;
					if (!isset($_SESSION['user']) && $host == 'localhost' && $user == 'pines' && $password == 'password' && $database == 'pines') {
						if ($pines->request_component != 'com_mysql')
							pines_redirect(pines_url('com_mysql', 'setup'));
					} else {
						if (function_exists('pines_error'))
							pines_error('Could not select database: ' . mysql_error());
					}
				}
			} else {
				$this->connected = false;
				if (!isset($_SESSION['user']) && $host == 'localhost' && $user == 'pines' && $password == 'password' && $database == 'pines') {
					if ($pines->request_component != 'com_mysql')
						pines_redirect(pines_url('com_mysql', 'setup'));
				} else {
					if (function_exists('pines_error'))
						pines_error('Could not connect: ' . mysql_error());
				}
			}
		}
		return $this->connected;
	}

	/**
	 * Disconnect from a MySQL database.
	 *
	 * @return bool Whether this instance is connected to a MySQL database after the method has run.
	 */
	public function disconnect() {
		if ($this->connected) {
			mysql_close($this->link);
			$this->connected = false;
		}
		return $this->connected;
	}
}

?>