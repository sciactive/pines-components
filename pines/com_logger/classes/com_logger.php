<?php
/**
 * com_logger class.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_logger main class.
 *
 * Logs activity to a file.
 *
 * @package Pines
 * @subpackage com_logger
 */
class com_logger extends component {
	/**
	 * A buffer to store info and notice level log messages temporarily.
	 *
	 * This should help reduce overuse of the filesystem.
	 * @var string
	 */
	var $tmp_log = '';

	/**
	 * Used to store the log file location, since it may not be available as
	 * the system is destroying $pines.
	 * @var string
	 */
	var $log_file = '';

	function __construct() {
		global $pines;
		$this->log_file = $pines->config->com_logger->path;
	}

	/**
	 * Write the $tmp_log buffer to disk.
	 */
	function __destruct() {
		if (strlen($this->tmp_log)) $this->write($this->tmp_log);
	}

	/**
	 * Set up a callback for each hook to log system activity.
	 *
	 * Also deletes the com_logger hooks, so logging won't recursively log
	 * itself. This function is called when log level is set to 'debug' in order
	 * to help diagnose problems with a component.
	 */
	function hook() {
		global $pines;
		$pines->hook->add_callback('all', -1, 'com_logger__hook_log');
	}

	/**
	 * Log an entry to the Pines log.
	 *
	 * @param string $message The message to be logged.
	 * @param string $level The level of the message. (debug, info, notice, warning, error, or fatal)
	 * @return bool True on success, false on error.
	 */
	function log($message, $level = 'info') {
		global $pines;
		$date = date('c');
		$user = is_object($_SESSION['user']) ? $_SESSION['user']->username.' ('.$_SESSION['user_id'].')' : $_SERVER['REMOTE_ADDR'];
		$location = $pines->component.', '.$pines->action;
		switch ($level) {
			case 'info':
				if (!in_array($pines->config->com_logger->level, array('debug', 'info'))) break;
			case 'notice':
				if (!in_array($pines->config->com_logger->level, array('debug', 'info', 'notice'))) break;
				if (strlen($this->tmp_log)) $this->tmp_log .= "\n";
				$this->tmp_log .= "$date: $level: $location: $user: $message";
				break;
			case 'debug':
				// Debug logs should be written immediately, since the system may halt at any time. ;)
				if (!in_array($pines->config->com_logger->level, array('debug'))) break;
			case 'warning':
				if (!in_array($pines->config->com_logger->level, array('debug', 'info', 'notice', 'warning'))) break;
			case 'error':
				if (!in_array($pines->config->com_logger->level, array('debug', 'info', 'notice', 'warning', 'error'))) break;
			case 'fatal':
				if (!in_array($pines->config->com_logger->level, array('debug', 'info', 'notice', 'warning', 'error', 'fatal'))) break;
				if (strlen($this->tmp_log)) $this->tmp_log .= "\n";
				$message = $this->tmp_log . "$date: $level: $location: $user: $message";
				$this->tmp_log = '';
				return $this->write($message);
				break;
		}
		return true;
	}

	/**
	 * Write log(s) to the media.
	 *
	 * @param string $logs Log message(s).
	 * @return bool True on success, false on failure.
	 */
	function write($logs) {
		if (@$handle = fopen($this->log_file, 'a')) {
			fwrite($handle, $logs."\n");
			fclose($handle);
			return true;
		}
		return false;
	}
}

?>