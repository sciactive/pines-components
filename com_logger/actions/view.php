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
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_logger', 'view', null, false));
	return;
}

$com_logger_view = new module('com_logger', 'view', 'content');
$com_logger_view->title = 'Displaying Log File: '.$config->com_logger->path;
$com_logger_view->log = file_get_contents($config->com_logger->path);
if (empty($com_logger_view->log)) $com_logger_view->log = 'Nothing to display.';

?>