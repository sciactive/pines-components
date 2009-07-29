<?php
/**
 * com_user's configuration.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->com_user = new DynamicConfig;

// Allows users to have empty passwords.
$config->com_user->empty_pw = false;

// Allows the creation of an admin user.
$config->com_user->create_admin = true;

// The secret necessary to create an admin user.
$config->com_user->create_admin_secret = '874jdiv8';

$config->ability_manager = new abilities;

?>