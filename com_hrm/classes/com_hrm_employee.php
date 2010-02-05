<?php
/**
 * com_hrm_employee class.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A employee.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_employee extends entity {
	/**
	 * Load an employee.
	 * @param int $id The ID of the employee to load, 0 for a new employee.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'employee');
		// Defaults.
		$this->address_type = 'us';
		$this->addresses = array();
		$this->attributes = array();
		$this->timeclock = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Calculate the time the employee has worked between two given times.
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

	/**
	 * Create a new instance.
	 */
	public static function factory() {
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
	 * Save the employee.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the employee.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->editor->load();
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_hrm', 'form_employee', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form for the employee to clockin.
	 * @return module The form's module.
	 */
	public function print_clockin() {
		$module = new module('com_hrm', 'clock', 'right');
		$module->entity = $this;

		return $module;
	}

	/* public function validate() {
		return array(
			'name' => array(
				'required' => 'Please specify a name.'
			),
			'email' => array(
				'required' => 'Please specify an email.',
				'email' => 'The email provided is not valid.'
			),
			'phone_cell' => array(
				'one_required' => array('phones', 'Please specify at least one phone number.'),
				'phone' => 'The phone number provided is not valid.'
			),
			'phone_work' => array(
				'one_required' => array('phones', 'Please specify at least one phone number.'),
				'phone' => 'The phone number provided is not valid.'
			),
			'phone_home' => array(
				'one_required' => array('phones', 'Please specify at least one phone number.'),
				'phone' => 'The phone number provided is not valid.'
			)
		);
	} */
}

?>