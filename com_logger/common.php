<?php
/**
 * com_logger's common file.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($pines->ability_manager) ) {
	$pines->ability_manager->add('com_logger', 'view', 'View Log', 'Let the user view the Pines log.');
	$pines->ability_manager->add('com_logger', 'clear', 'Clear Log', 'Let the user clear (delete) the pines log.');
}

/**
 * Log a hooked function call.
 *
 * @param array $return The return values for the hook.
 * @param string $hook The hook that was called.
 * @return array The return values for the hook.
 */
function com_logger_hook_log($return, $hook) {
	global $pines;
	if (!in_array($hook, array('$pines->log_manager->log', '$pines->log_manager->hook', '$pines->log_manager->write')))
		$pines->log_manager->log('(microtime='.microtime(true).') '.$hook, 'debug');
	return $return;
}

if ($pines->com_logger->level == 'debug') $pines->log_manager->hook();

?>