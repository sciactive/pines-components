<?php
/**
 * com_mysql's common file.
 *
 * @package Pines
 * @subpackage com_mysql
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
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
    public $connected = false;
    public $link = null;

    public function __construct($host = null, $user = null, $password = null, $database = null) {
        $this->connect($host, $user, $password, $database);
    }

	function connect($host = null, $user = null, $password = null, $database = null) {
		global $config;
        /**
         * If something changes the host, it could reveal the user and password.
         */
        if (is_null($host)) $host = $config->com_mysql->host;
        if (is_null($user)) $user = $config->com_mysql->user;
        if (is_null($password)) $password = $config->com_mysql->password;
        if (is_null($database)) $database = $config->com_mysql->database;
		// Connecting, selecting database
		if (!$this->connected) {
			$this->link = mysql_connect($host, $user, $password) or die('Could not connect: ' . mysql_error());
			mysql_select_db($database, $this->link) or die('Could not select database: ' . mysql_error());
			$this->connected = true;
		}
	}

	function disconnect() {
		if ($this->connected) {
			mysql_close($this->link);
			$this->connected = false;
		}
	}
}

$config->db_manager = new com_mysql;

?>