<?php
/**
 * com_logger class.
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
 * com_logger main class.
 *
 * Logs activity to a file.
 *
 * @package Components\logger
 */
class com_logger extends component implements log_manager_interface {
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

	public function __construct() {
		global $pines;
		$this->log_file = $pines->config->com_logger->path;
	}

	/**
	 * Write the $tmp_log buffer to disk.
	 */
	public function __destruct() {
		if (strlen($this->tmp_log)) $this->write($this->tmp_log);
	}

	public function log($message, $level = 'info') {
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
	 * Concatenate all log files.
	 * @return string The concatenated text from all log files.
	 */
	public function cat_logs() {
		global $pines;

		// Get all log files' paths.
		$files = glob($pines->config->com_logger->read_pattern, GLOB_MARK);

		// Now go through and concatenate each file.
		$log_data = '';
		if ($pines->config->com_logger->read_include_path && !in_array($pines->config->com_logger->path, $files)) {
			$log_data .= file_get_contents($pines->config->com_logger->path);
			if (substr($log_data, -1) != "\n")
				$log_data .= "\n";
		}
		foreach ($files as $cur_file) {
			if ($cur_file == '.' || $cur_file == '..')
				continue;
			// This will work for regular files and gzip encoded files.
			if (!($r = gzopen($cur_file, 'r')))
				return false;
			do {
				$log_data .= gzread($r, 8192);
				//TO DO: Warn when there are too many logs, 
				// or put this data into an array - 
				// string size overflow will happen here depending on how many logs
				// and memory allocation in php.ini
			} while (!gzeof($r));
		}

		// All done.
		return $log_data;
	}

	/**
	 * Write log(s) to the media.
	 *
	 * @param string $logs Log message(s).
	 * @return bool True on success, false on failure.
	 */
	public function write($logs) {
		if (@$handle = fopen($this->log_file, 'a')) {
			fwrite($handle, $logs."\n");
			fclose($handle);
			return true;
		}
		return false;
	}

	/**
	 * Print a form to select date timespan.
	 *
	 * @param bool $all_time Currently searching all records or a timespan.
	 * @param string $start The current starting date of the timespan.
	 * @param string $end The current ending date of the timespan.
	 * @return module The form's module.
	 */
	public function date_select_form($all_time = false, $start = null, $end = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_logger', 'date_selector', 'content');
		$module->all_time = $all_time;
		$module->start_date = $start;
		$module->end_date = $end;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Print a form to select a location.
	 *
	 * @param int $location The currently set location to search in.
	 * @param bool $descendants Whether to show descendant locations.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null, $descendants = false) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_logger', 'location_selector', 'content');
		if (!isset($location)) {
			$module->location = $_SESSION['user']->group->guid;
		} else {
			$module->location = $location;
		}
		$module->descendants = $descendants;

		$pines->page->override_doc($module->render());
		return $module;
	}
}

?>