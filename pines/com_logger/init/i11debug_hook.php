<?php
/**
 * Hook all methods if log level is debug.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Log a hooked function call.
 *
 * @param array $return The return values for the hook.
 * @param string $hook The hook that was called.
 * @return array The return values for the hook.
 */
function com_logger__hook_log($return, $hook) {
	global $pines;
	if (!in_array($hook, array('$pines->log_manager->log', '$pines->log_manager->hook', '$pines->log_manager->write')))
		$pines->log_manager->log('(microtime='.microtime(true).') '.$hook, 'debug');
	return $return;
}

if ($pines->config->com_logger->level == 'debug') $pines->log_manager->hook();

?>