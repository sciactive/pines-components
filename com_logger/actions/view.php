<?php
/**
 * Display the log.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_logger/view') )
	punt_user(null, pines_url('com_logger', 'view'));

$view = new module('com_logger', 'view', 'content');
if (file_exists($pines->config->com_logger->path)) {
	if (($view->log = file_get_contents($pines->config->com_logger->path)) === false)
		pines_error('Error reading log file '.$pines->config->com_logger->path);
} else {
	pines_error('Log file '.$pines->config->com_logger->path.' does not exist!');
}
if (empty($view->log)) $view->log = 'Log file is empty.';

?>