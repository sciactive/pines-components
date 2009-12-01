<?php
/**
 * Clear the log.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_logger/clear') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_logger', 'clear', null, false));
	return;
}

if (file_put_contents($config->com_logger->path, '') !== false) {
	display_notice('Log file cleared.');
} else {
	display_error('Error writing to log file.');
}

action('com_logger', 'view');

?>