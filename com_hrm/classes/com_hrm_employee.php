<?php
/**
 * com_hrm_employee class.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An employee.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_employee extends user {
	/**
	 * Load an employee.
	 * @param int $id The ID of the employee to load, 0 for a new employee.
	 */
	public function __construct($id = 0) {
		// Defaults.
		$this->employee = true;
		$this->timeclock = array();
		$this->employee_attributes = array();

		parent::__construct($id);
	}

	/**
	 * Calculate the time the employee has worked between two given times.
	 *
	 * @param int $time_start Unix time stamp of start time.
	 * @param int $time_end Unix time stamp of end time.
	 * @return int Number of seconds worked.
	 */
	public function time_sum($time_start = null, $time_end = null) {
		if ((array) $this->timeclock !== $this->timeclock)
			return 0;
		// We need to copy the array because the entity won't keep track of the array pointer.
		$timeclock = $this->timeclock;
		$time = 0;
		for ($cur_entry = reset($timeclock); current($timeclock); $cur_entry = next($timeclock)) {
			if ($cur_entry['status'] == 'in') {
				// Start at the in time, or $start_time if it comes after.
				$cur_start = (isset($time_start) && $cur_entry['time'] < $time_start) ? $time_start : $cur_entry['time'];
				// Skip in times after the end date.
				if (isset($time_end) && $cur_entry['time'] > $time_end)
					continue;
				// Find the next out time.
				do {
					$next_entry = next($timeclock);
				} while ($next_entry && $next_entry['status'] != 'out');
				if ($next_entry) {
					// If there's an out time, use it, or $time_end.
					$cur_time = (isset($time_end) && $next_entry['time'] > $time_end ? $time_end : $next_entry['time']) - $cur_start;
					if ($cur_time > 0)
						$time += $cur_time;
				} else {
					// If there's no out time, use current time, or $time_end.
					$cur_time = (isset($time_end) && time() > $time_end ? $time_end : time()) - $cur_start;
					if ($cur_time > 0)
						$time += $cur_time;
				}
			}
		}
		return $time;
	}

	/**
	 * Create a new instance.
	 *
	 * @param int|string $id The ID or username of the employee to load, 0 for a new employee.
	 * @return com_hrm_employee The new instance.
	 */
	public static function factory($id = 0) {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the employee.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted employee $this->name.", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the employee.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_hrm', 'employee/form', 'content');
		$module->entity = $this;
		$module->user_templates = $pines->entity_manager->get_entities(array('class' => com_hrm_user_template), array('&', 'tag' => array('com_hrm', 'user_template')));

		return $module;
	}

	/**
	 * Print a form to edit the employee's timeclock.
	 * @return module The form's module.
	 */
	public function print_timeclock() {
		$module = new module('com_hrm', 'employee/timeclock/form', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a module to see the employee's timeclock.
	 * @return module The module.
	 */
	public function print_timeclock_view() {
		$module = new module('com_hrm', 'employee/timeclock/view', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form for the employee to clockin.
	 * @return module The form's module.
	 */
	public function print_clockin() {
		$module = new module('com_hrm', 'employee/timeclock/clock', 'right');
		$module->entity = $this;

		return $module;
	}
}

?>