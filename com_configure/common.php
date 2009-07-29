<?php
/**
 * com_configure's common file.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($config->ability_manager) ) {
	$config->ability_manager->add('com_configure', 'manage', 'Manage Configuration', 'Let the user change configuration settings.');
	$config->ability_manager->add('com_configure', 'view', 'View Configuration', 'Let the user see current configuration settings.');
}

$config->configurator = new com_configure;

?>