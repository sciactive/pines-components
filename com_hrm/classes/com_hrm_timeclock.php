<?php
/**
 * com_hrm_timeclock class.
 *
 * @package Components
 * @subpackage hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An employee's timeclock.
 *
 * @package Components
 * @subpackage hrm
 */
class com_hrm_timeclock extends entity {
	/**
	 * Load a timeclock.
	 * @param int $id The ID of the timeclock to load, 0 for a new timeclock.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'timeclock');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		$this->ac = (object) array('user' => 3, 'group' => 3, 'other' => 2);
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

	/**
	 * Add a time entry.
	 * @param int $time_in The time in.
	 * @param int $time_out The time out.
	 * @param string $comment An optional comment.
	 * @param array $extras Any extra values to store.
	 * @return bool True on success, false on failure.
	 */
	public function add($time_in, $time_out, $comment = '', $extras = array()) {
		global $pines;
		// Check that this time doesn't conflict with any other times.
		$check = $pines->entity_manager->get_entity(
				array('class' => com_hrm_timeclock_entry),
				array('&',
					'tag' => array('com_hrm', 'timeclock_entry'),
					'ref' => array('user', $this->user),
					'lt' => array('in', $time_out),
					'gt' => array('out', $time_in)
				)
			);
		if (isset($check->guid))
			return false;
		$entity = com_hrm_timeclock_entry::factory();
		$entity->in = (int) $time_in;
		$entity->out = (int) $time_out;
		$entity->comment = (string) $comment;
		$entity->extras = (array) $extras;
		if (!$entity->save())
			return false;
		$entity->user = $this->user;
		$entity->group = $this->group;
		return $entity->save();
	}

	/**
	 * Clock the user in.
	 * @return bool True on success, false on failure.
	 */
	public function clock_in() {
		if (isset($this->time_in))
			return false;
		$this->time_in = time();
		$this->ip_in = $_SERVER['REMOTE_ADDR'];
		$this->ua_in = $_SERVER['HTTP_USER_AGENT'];
		return $this->save();
	}

	/**
	 * Clock the user out.
	 * @param string $comment An optional comment.
	 * @return bool True on success, false on failure.
	 */
	public function clock_out($comment = '') {
		if (!isset($this->time_in))
			return false;
		$time_in = $this->time_in;
		$time_out = time();
		if ($this->add($time_in, $time_out, $comment,
				array(
					'ip_in' => $this->ip_in,
					'ip_out' => $_SERVER['REMOTE_ADDR'],
					'ua_in' => $this->ua_in,
					'ua_out' => $_SERVER['HTTP_USER_AGENT']
				)
			)) {
			unset($this->time_in);
			return $this->save();
		} else
			return false;
	}

	/**
	 * Get the time the user clocked in.
	 * 
	 * Returns null if the user is not clocked in.
	 * 
	 * @return int The clock in time.
	 */
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
	 *
	 * @param int $time_start Unix time stamp of start time.
	 * @param int $time_end Unix time stamp of end time.
	 * @return module The form's module.
	 */
	public function print_timeclock($time_start, $time_end) {
		global $pines;
		$module = new module('com_hrm', 'employee/timeclock/form', 'content');
		$module->entity = $this;
		$module->time_start = (int) $time_start;
		$module->time_end = (int) $time_end;
		// Get the matching entries.
		$module->entries = $pines->entity_manager->get_entities(
				array('class' => com_hrm_timeclock_entry),
				array('&',
					'tag' => array('com_hrm', 'timeclock_entry'),
					'ref' => array('user', $this->user),
					'lt' => array('in', $time_end),
					'gt' => array('out', $time_start)
				)
			);

		return $module;
	}

	/**
	 * Print a module to see the employee's timeclock.
	 *
	 * @param int $time_start Unix time stamp of start time.
	 * @param int $time_end Unix time stamp of end time.
	 * @return module The module.
	 */
	public function print_timeclock_view($time_start = null, $time_end = null) {
		global $pines;
		$module = new module('com_hrm', 'employee/timeclock/view', 'content');
		$module->entity = $this;
		$module->time_start = $time_start;
		$module->time_end = $time_end;
		// Get the matching entries.
		$selector = array('&',
				'tag' => array('com_hrm', 'timeclock_entry'),
				'ref' => array('user', $this->user)
			);
		if (isset($time_start) && isset($time_end)) {
			$selector['lt'] = array('in', $time_end);
			$selector['gt'] = array('out', $time_start);
		}
		$module->entries = $pines->entity_manager->get_entities(
				array('class' => com_hrm_timeclock_entry),
				$selector
			);

		return $module;
	}

	/**
	 * Calculate the time the employee has worked between two given times.
	 * 
	 * Leave both times null to sum all time.
	 *
	 * @param int $time_start Unix time stamp of start time.
	 * @param int $time_end Unix time stamp of end time.
	 * @param bool $include_new Whether to include time from the employee's current clockin, if they're clocked in.
	 * @return int Number of seconds worked.
	 */
	public function sum($time_start = null, $time_end = null, $include_new = true) {
		global $pines;
		$selector = array('&',
				'tag' => array('com_hrm', 'timeclock_entry'),
				'ref' => array('user', $this->user)
			);
		if (isset($time_start) && isset($time_end)) {
			$selector['lt'] = array('in', $time_end);
			$selector['gt'] = array('out', $time_start);
		}
		$entries = $pines->entity_manager->get_entities(
				array('class' => com_hrm_timeclock_entry),
				$selector
			);
		$time = 0;
		foreach ($entries as $cur_entry) {
			if (($cur_entry->in >= $time_start && $cur_entry->out <= $time_end) || (!isset($time_start) && !isset($time_end))) {
				// The whole entry counts.
				$time += $cur_entry->out - $cur_entry->in;
			} elseif ($cur_entry->in >= $time_start && $cur_entry->in < $time_end) {
				// The beginning of the entry counts.
				$time += $time_end - $cur_entry->in;
			} elseif ($cur_entry->out > $time_start && $cur_entry->out <= $time_end) {
				// The end of the entry counts.
				$time += $cur_entry->out - $time_start;
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