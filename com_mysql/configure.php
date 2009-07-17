<?php
/**
 * com_mysql's configuration.
 *
 * @package XROOM
 * @subpackage com_mysql
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

$config->com_mysql = new DynamicConfig;

// Database Settings
$config->com_mysql->host = "localhost";

$config->com_mysql->user = "xroom";

$config->com_mysql->password = "password";

$config->com_mysql->database = "xroom";

$config->com_mysql->prefix = "ddl_";
?>