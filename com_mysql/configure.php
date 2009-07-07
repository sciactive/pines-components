<?php
defined('D_RUN') or die('Direct access prohibited');

$config->com_mysql = new DynamicConfig;

// Database Settings
$config->com_mysql->host = "localhost";

$config->com_mysql->user = "dandelion";

$config->com_mysql->password = "password";

$config->com_mysql->database = "dandelion";

$config->com_mysql->prefix = "ddl_";
?>