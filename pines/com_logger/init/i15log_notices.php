<?php
/**
 * Take over the notice functions to log them.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!function_exists('display_error') && $pines->config->com_logger->log_errors) {
	/**
	 * Log a displayed error.
	 *
	 * @param string $error_text The error text.
	 * @ignore
	 */
	function display_error($error_text) {
		global $pines;
		$pines->log_manager->log($error_text, 'error');
		$pines->page->error($error_text);
	}
}

if (!function_exists('display_notice') && $pines->config->com_logger->log_notices) {
	/**
	 * Log a displayed notice.
	 *
	 * @param string $notice_text The notice text.
	 * @ignore
	 */
	function display_notice($notice_text) {
		global $pines;
		$pines->log_manager->log($notice_text, 'notice');
		$pines->page->notice($notice_text);
	}
}

?>