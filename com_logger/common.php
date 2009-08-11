<?php
/**
 * com_logger's common file.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($config->ability_manager) ) {
	$config->ability_manager->add('com_logger', 'view', 'View Log', 'Let the user view the Pines log.');
	$config->ability_manager->add('com_logger', 'clear', 'Clear Log', 'Let the user clear (delete) the pines log.');
}

if ($config->com_logger->level == 'debug') $config->log_manager->hook();

?>