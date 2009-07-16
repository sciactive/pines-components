<?php
/**
 * com_mysql's common file.
 *
 * @package Dandelion
 * @subpackage com_mysql
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

$com_mysql = new DynamicConfig;

if (!isset($com_mysql->connected))
	$com_mysql->connected = false;

if (!isset($com_mysql->link))
	$com_mysql->link = 0;

class com_mysql {
	function connect() {
		global $com_mysql, $config;
		// Connecting, selecting database
		if (!$com_mysql->connected) {
			$com_mysql->link = mysql_connect($config->com_mysql->host, $config->com_mysql->user, $config->com_mysql->password) or die('Could not connect: ' . mysql_error());
			mysql_select_db($config->com_mysql->database, $com_mysql->link) or die('Could not select database: ' . mysql_error());
			$com_mysql->connected = true;
		}
	}

	function disconnect() {
		global $com_mysql;
		if ($com_mysql->connected) {
			mysql_close($com_mysql->link);
			$com_mysql->connected = false;
		}
	}
}

$config->db_manager = new com_mysql;

?>