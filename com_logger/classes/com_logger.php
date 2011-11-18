<?php
/**
 * com_logger class.
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

/**
 * com_logger main class.
 *
 * Logs activity to a file.
 *
 * @package Pines
 * @subpackage com_logger
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
	 * Creates and attaches a module which summarizes employee totals.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @return module the log view module.
	 */
	function logs($start_date = null, $end_date = null) {
		global $pines;

		$module = new module('com_logger', 'logs', 'content');

		$selector = array('&');
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		
		return $module;
	}
}

?>