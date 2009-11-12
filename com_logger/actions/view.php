<?php
/**
 * Display the log.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_logger/view') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_logger', 'view', null, false));
	return;
}

$view = new module('com_logger', 'view', 'content');
if (file_exists($config->com_logger->path)) {
	if (!($view->log = file_get_contents($config->com_logger->path)))
		display_error('Error reading log file '.$config->com_logger->path);
} else {
	display_error('Log file '.$config->com_logger->path.' does not exist!');
}
if (empty($view->log)) $view->log = 'Nothing to display.';

?>