<?php
/**
 * Display the raw log.
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
	punt_user(null, pines_url('com_logger', 'rawlog'));

$view = new module('com_logger', 'rawlog', 'content');
// Check that the log file exists.
if (!file_exists($pines->config->com_logger->path))
	pines_error('Log file '.$pines->config->com_logger->path.' does not exist!');
// Get all the logs.
if (($view->log = $pines->log_manager->cat_logs()) === false)
	pines_error('Error reading log files.');
if (empty($view->log)) $view->log = 'Log file is empty.';

?>