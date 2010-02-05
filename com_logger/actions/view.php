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

if ( !gatekeeper('com_logger/view') )
	punt_user('You don\'t have necessary permission.', pines_url('com_logger', 'view', null, false));

$view = new module('com_logger', 'view', 'content');
if (file_exists($pines->com_logger->path)) {
	if (($view->log = file_get_contents($pines->com_logger->path)) === false)
		display_error('Error reading log file '.$pines->com_logger->path);
} else {
	display_error('Log file '.$pines->com_logger->path.' does not exist!');
}
if (empty($view->log)) $view->log = 'Log file is empty.';

?>