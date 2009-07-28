<?php
/**
 * com_mysql's configuration.
 *
 * @package Pines
 * @subpackage com_mysql
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->com_mysql = new DynamicConfig;

/**
 * Database Settings
 */
    /**
     * The default MySQL host.
     */
    $config->com_mysql->host = "localhost";
    /**
     * The default MySQL user.
     */
    $config->com_mysql->user = "pines";
    /**
     * The default MySQL password.
     */
    $config->com_mysql->password = "password";
    /**
     * The default MySQL database.
     */
    $config->com_mysql->database = "pines";
    /**
     * The default MySQL table name prefix.
     */
    $config->com_mysql->prefix = "pin_";
?>