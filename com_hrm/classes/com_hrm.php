<?php
/**
 * com_hrm class.
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
 * com_hrm main class.
 *
 * Provides an HR manager.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm extends component {
	/**
	 * Whether to integrate with com_sales.
	 *
	 * @var bool $com_sales
	 */
	var $com_sales;

	/**
	 * Check whether com_sales is installed and we should integrate with it.
	 *
	 * Places the result in $this->com_sales.
	 */
	public function __construct() {
		global $pines;
		$this->com_sales = $pines->depend->check('component', 'com_sales');
	}

	/**
	 * Clears all events from the calendar.
	 */
	public function clear_calendar() {
		global $pines;
		$calendar_events = $pines->entity_manager->get_entities(array('class' => com_hrm_event), array('&', 'tag' => array('com_hrm', 'event')));
		foreach ($calendar_events as $cur_event)
			$cur_event->delete();
	}

	/**
	 * Get all employees.
	 *
	 * @return array An array of employees.
	 * @todo Optimize this function.
	 */
	public function get_employees() {
		global $pines;
		$users = $pines->user_manager->get_users();
		$employees = array();
		// Filter out users who aren't employees.
		foreach ($users as $key => &$cur_user) {
			if ($cur_user->employee)
				$employees[] = com_hrm_employee::factory($cur_user->guid);
			unset($users[$key]);
		}
		unset($cur_user);
		return $employees;
	}

	/**
	 * Creates and attaches a module which lists employees.
	 */
	public function list_employees() {
		$module = new module('com_hrm', 'employee/list', 'content');

		$module->employees = $this->get_employees();

		if ( empty($module->employees) )
			pines_notice('There are no employees.');
	}

	/**
	 * Creates and attaches a module which lists employees' timeclocks.
	 */
	public function list_timeclocks() {
		$module = new module('com_hrm', 'employee/timeclock/list', 'content');

		$module->employees = $this->get_employees();

		if ( empty($module->employees) )
			pines_notice('There are no employees.');
	}

	/**
	 * Print a form to select a company location.
	 *
	 * @param int $location The current ending date of the timespan.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null) {
		global $pines;
		$pines->page->override = true;

		if (!isset($location))
			$location = $_SESSION['user']->group->guid;
		$module = new module('com_hrm', 'form_schedule');
		$module->location = $location;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Creates and attaches a module which lists all pending time off requests.
	 */
	public function review_timeoff() {
		global $pines;
		$pines->page->override = true;
		$module = new module('com_hrm', 'timeoff/review', 'content');
		$module->requests = $pines->entity_manager->get_entities(array('class' => com_hrm_rto), array('&', 'data' => array('status', 'pending'), 'tag' => array('com_hrm', 'rto')));
		$pines->page->override_doc($module->render());
	}
	
	/**
	 * Creates and attaches a module which shows the calendar.
	 * @param int $id An event GUID.
	 * @param group $location The desired location to view the schedule for.
	 * @param int $rto A time off request GUID.
	 */
	public function show_calendar($id = null, $location = null, $rto = null) {
		global $pines;

		if (!isset($location) || !isset($location->guid))
			$location = $_SESSION['user']->group;

		$form = new module('com_hrm', 'form_calendar', 'right');
		// If an id is specified, the event info will be displayed for editing.
		if (isset($id) && $id >  0) {
			$form->entity = com_hrm_event::factory((int) $id);
			$location = $form->entity->group;
		}
		// Should work like this, we need to have the employee's group update upon saving it to a user.
		$form->employees = $this->get_employees();
		$form->location = $location->guid;
		if (isset($rto) && $rto >  0)
			$form->rto = com_hrm_rto::factory((int) $rto);

		$calendar_head = new module('com_hrm', 'show_calendar_head', 'head');
		$calendar = new module('com_hrm', 'show_calendar', 'content');
		$calendar->events = $pines->entity_manager->get_entities(array('class' => com_hrm_event), array('&', 'ref' => array('group', $location), 'tag' => array('com_hrm', 'event')));
		$calendar->location = $location;
	}

	/**
	 * Sort users.
	 * @param group $a User.
	 * @param group $b User.
	 * @return bool User order.
	 */
	private function sort_users($a, $b) {
		$aname = empty($a->name) ? $a->username : $a->name;
		$bname = empty($b->name) ? $b->username : $b->name;
		return strtolower($aname) > strtolower($bname);
	}

	/**
	 * Print a form to select users.
	 *
	 * @param bool $all Whether to show users who are employees too.
	 * @return module The form's module.
	 */
	public function user_select_form($all = false) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_hrm', 'forms/users');
		$module->users = (array) $pines->user_manager->get_users();
		if (!$all) {
			// Filter out users who are already employees.
			foreach ($module->users as $key => &$cur_user) {
				if ($cur_user->employee)
					unset($module->users[$key]);
			}
			unset($cur_user);
		}
		usort($module->users, array($this, 'sort_users'));

		$pines->page->override_doc($module->render());
		return $module;
	}
}

?>