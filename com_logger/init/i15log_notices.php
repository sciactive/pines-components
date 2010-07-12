<?php
/**
 * Take over the notice functions to log them.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_logger->log_errors) {
	/**
	 * Log a displayed error.
	 *
	 * @param string &$args The error text.
	 */
	function com_logger__log_error(&$args) {
		global $pines;
		$pines->log_manager->log($args[0], 'error');
	}
	$pines->hook->add_callback('$pines->page->error', -100, 'com_logger__log_error');
}

if ($pines->config->com_logger->log_notices) {
	/**
	 * Log a displayed notice.
	 *
	 * @param string &$args The notice text.
	 */
	function com_logger__log_notice(&$args) {
		global $pines;
		$pines->log_manager->log($args[0], 'notice');
	}
	$pines->hook->add_callback('$pines->page->notice', -100, 'com_logger__log_notice');
}

?>