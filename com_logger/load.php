<?php
/**
 * com_logger's loader file.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->log_manager = new com_logger;

if (!function_exists('display_error')) {
	function display_error($error_text) {
		global $config, $page;
        $config->log_manager->log($error_text, 'error');
		$page->error($error_text);
	}
}

if (!function_exists('display_notice')) {
	function display_notice($notice_text) {
		global $config, $page;
        $config->log_manager->log($notice_text, 'notice');
		$page->notice($notice_text);
	}
}

?>