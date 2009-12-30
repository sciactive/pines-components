<?php
/**
 * com_sales_employee_data class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Data for an employee.
 *
 * @package Pines
 * @subpackage com_sales
 * @todo Clean up this extra database info after a user is deleted.
 */
class com_sales_employee_data extends entity {
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_sales', 'employee_data');
	}

	/**
	 * Calculate the time an employee has worked between two given times.
	 *
	 * @param int $time_start Unix time stamp of start time.
	 * @param int $time_end Unix time stamp of end time.
	 * @return int Number of seconds worked.
	 */
	public function time_sum($time_start = null, $time_end = null) {
		if (!is_array($this->timeclock))
			return 0;
		// We need to copy the array because the entity won't keep track of the array pointer.
		$timeclock = $this->timeclock;
		$time = 0;
		for ($cur_entry = reset($timeclock); current($timeclock); $cur_entry = next($timeclock)) {
			if ($cur_entry['status'] == 'in') {
				// Start at the in time, or $start_time if it comes after.
				$cur_start = (!is_null($time_start) && $cur_entry['time'] < $time_start) ? $time_start : $cur_entry['time'];
				// Skip in times after the end date.
				if (!is_null($time_end) && $cur_entry['time'] > $time_end)
					continue;
				// Find the next out time.
				do {
					$next_entry = next($timeclock);
				} while ($next_entry && $next_entry['status'] != 'out');
				if ($next_entry) {
					// If there's an out time, use it, or $time_end.
					$cur_time = (!is_null($time_end) && $next_entry['time'] > $time_end ? $time_end : $next_entry['time']) - $cur_start;
					if ($cur_time > 0)
						$time += $cur_time;
				} else {
					// If there's no out time, use current time, or $time_end.
					$cur_time = (!is_null($time_end) && time() > $time_end ? $time_end : time()) - $cur_start;
					if ($cur_time > 0)
						$time += $cur_time;
				}
			}
		}
		return $time;
	}
}

?>