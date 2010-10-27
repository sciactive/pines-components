<?php
/**
 * com_hrm_timeclock class.
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
 * An employee's timeclock.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_timeclock extends entity {
	/**
	 * Load a timeclock.
	 * @param int $id The ID of the timeclock to load, 0 for a new timeclock.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'timeclock');
		// Defaults.
		$this->timeclock = array();
		$this->ac = (object) array('user' => 3, 'group' => 3, 'other' => 2);
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data(), $entity->get_sdata());
		}
	}

	/**
	 * Create a new instance.
	 * @return com_hrm_timeclock The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	public function add($time_in, $time_out, $comment = '') {
		// Check that this time doesn't conflict with any other times.
		foreach ($this->timeclock as $cur_entry) {
			if ($time_in < $cur_entry['out'] && $time_out > $cur_entry['in'])
				return false;
		}
		$this->timeclock[] = array('in' => (int) $time_in, 'out' => (int) $time_out, 'comment' => (string) $comment);
		return true;
	}
	
	public function clock_in() {
		if (isset($this->time_in))
			return false;
		$this->time_in = time();
		return true;
	}
	
	public function clock_out($comment = '') {
		if (!isset($this->time_in))
			return false;
		$time_in = $this->time_in;
		$time_out = time();
		unset($this->time_in);
		return $this->add($time_in, $time_out, $comment);
	}
	
	public function clocked_in_time() {
		return $this->time_in;
	}

	/**
	 * Print a form for the employee to clock in.
	 * @return module The form's module.
	 */
	public function print_clockin() {
		$module = new module('com_hrm', 'employee/timeclock/clock', 'right');
		$module->entity = $this;

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
	 * Calculate the time the employee has worked between two given times.
	 *
	 * @param int $time_start Unix time stamp of start time.
	 * @param int $time_end Unix time stamp of end time.
	 * @param bool $include_new Whether to include time from the employee's current clockin, if they're clocked in.
	 * @return int Number of seconds worked.
	 */
	public function sum($time_start = null, $time_end = null, $include_new = true) {
		if ((array) $this->timeclock !== $this->timeclock)
			return 0;
		$time = 0;
		foreach ($this->timeclock as $cur_entry) {
			if (($cur_entry['in'] >= $time_start && $cur_entry['out'] <= $time_end) || (!isset($time_start) && !isset($time_end))) {
				// The whole entry counts.
				$time += $cur_entry['out'] - $cur_entry['in'];
			} elseif ($cur_entry['in'] >= $time_start && $cur_entry['in'] < $time_end) {
				// The beginning of the entry counts.
				$time += $time_end - $cur_entry['in'];
			} elseif ($cur_entry['out'] > $time_start && $cur_entry['out'] <= $time_end) {
				// The end of the entry counts.
				$time += $cur_entry['out'] - $time_start;
			}
		}
		if ($include_new && isset($this->time_in) && ($this->time_in < $time_end || !isset($time_end))) {
			if (!isset($time_start) && !isset($time_end)) {
				$time += time() - $this->time_in;
			} elseif ($this->time_in >= $time_start) {
				$time += $time_end - $this->time_in;
			} else {
				$time += $time_end - $time_start;
			}
		}
		return $time;
	}
}

?>