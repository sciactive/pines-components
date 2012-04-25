<?php
/**
 * Hook all methods if log level is debug.
 *
 * @package Components\logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Log a hooked function call.
 *
 * @param array $return The return values for the hook.
 * @param string $hook The hook that was called.
 */
function com_logger__hook_log($return, $hook) {
	global $pines;
	if (!in_array($hook, array('$pines->log_manager->log', '$pines->log_manager->write')))
		$pines->log_manager->log('(microtime='.microtime(true).') '.$hook, 'debug');
}

/*
 * Set up a callback for all hooks to log system activity.
 *
 * This is done when log level is set to 'debug' in order to help diagnose
 * problems with a component.
 */
if ($pines->config->com_logger->level == 'debug')
	$pines->hook->add_callback('all', -1, 'com_logger__hook_log');

?>