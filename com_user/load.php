<?php
/**
 * com_user's loader.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The user manager.
 * @global com_user $config->user_manager
 */
$config->user_manager = new com_user;

/**
 * The ability manager.
 * @global abilities $config->ability_manager
 */
$config->ability_manager = new abilities;

?>