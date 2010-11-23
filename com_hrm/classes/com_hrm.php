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
	 * @param bool $employed Get currently employed or past employees.
	 * @return array An array of employees.
	 * @todo Optimize this function.
	 */
	public function get_employees($employed = true) {
		global $pines;
		$users = $pines->user_manager->get_users();
		$employees = array();
		// Filter out users who aren't employees.
		foreach ($users as $key => &$cur_user) {
			if ($cur_user->employee && ($employed xor $cur_user->terminated))
				$employees[] = com_hrm_employee::factory($cur_user->guid);
			unset($users[$key]);
		}
		unset($cur_user);
		return $employees;
	}

	/**
	 * Get all issue types.
	 *
	 * @return array An array of issue types.
	 */
	public function get_issue_types() {
		global $pines;
		return $pines->entity_manager->get_entities(array('class' => com_hrm_issue_type), array('&', 'tag' => array('com_hrm', 'issue_type')));
	}

	/**
	 * Print a form to create a work lineup for a location.
	 * @param group $location The current location.
	 * @return module The form's module.
	 */
	public function lineup_form($location = null) {
		global $pines;
		$pines->page->override = true;

		if (!isset($location->guid))
			$location = $_SESSION['user']->group;

		$module = new module('com_hrm', 'form_lineup', 'content');
		$module->location = $location;
		$module->employees = $this->get_employees();

		$pines->page->override_doc($module->render());
	}

	/**
	 * Creates and attaches a module which lists employees.
	 * 
	 * @param bool $employed List currently employed or past employees.
	 * @return module The list module.
	 */
	public function list_employees($employed = true) {
		$module = new module('com_hrm', 'employee/list', 'content');
		
		$module->employees = $this->get_employees($employed);
		$module->employed = $employed;

		if ( empty($module->employees) )
			pines_notice('There are no matching employees.');
	}

	/**
	 * Creates and attaches a module which lists issue types.
	 */
	public function list_issue_types() {
		global $pines;

		$module = new module('com_hrm', 'issue/list', 'content');
		$module->types = $pines->entity_manager->get_entities(array('class' => com_hrm_issue_type), array('&', 'tag' => array('com_hrm', 'issue_type')));

		if ( empty($module->types) )
			pines_notice('There are no issue types.');
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
	 * @param int $location The current location.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null) {
		global $pines;
		$pines->page->override = true;

		if (!isset($location))
			$location = $_SESSION['user']->group->guid;
		$module = new module('com_hrm', 'form_location');
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
		$module->requests = $pines->entity_manager->get_entities(array('class' => com_hrm_rto), array('&', 'tag' => array('com_hrm', 'rto'), 'data' => array('status', 'pending')));
		$pines->page->override_doc($module->render());
	}
	
	/**
	 * Creates and attaches a module which shows the calendar.
	 * @param int $id An event GUID.
	 * @param group $location The desired location to view the schedule for.
	 * @param com_hrm_employee $employee The desired employee to view the schedule for.
	 * @param int $rto A time off request GUID.
	 */
	public function show_calendar($id = null, $location = null, $employee = null, $rto = null) {
		global $pines;

		if (!isset($location) || !isset($location->guid)) {
			$location = $_SESSION['user']->group;
			if (!isset($employee->guid))
				$employee = $_SESSION['user'];
		}

		$calendar_head = new module('com_hrm', 'show_calendar_head', 'head');
		$calendar = new module('com_hrm', 'show_calendar', 'content');
		$form = new module('com_hrm', 'form_calendar', 'right');
		// If an id is specified, the event info will be displayed for editing.
		if (isset($id) && $id >  0) {
			$form->entity = com_hrm_event::factory((int) $id);
			$location = $form->entity->group;
		}

		$selector = array('&', 'tag' => array('com_hrm', 'event'));
		$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		$ancestors = array();
		if (isset($employee->guid)) {
			unset($selector['ref']);
			$selector['ref'][] = array('employee', $employee);
			$form->employee = $calendar->employee = $employee;
			$location = $employee->group;
			$ancestors = $location->get_descendents(true);
		}
		if (isset($rto) && $rto >  0)
			$form->rto = com_hrm_rto::factory((int) $rto);

		// Should work like this, we need to have the employee's group update upon saving it to a user.
		$form->employees = $this->get_employees();
		$calendar->location = $form->location = $location;
		$calendar->events = $pines->entity_manager->get_entities(array('class' => com_hrm_event), $selector, $or);
		// Retrieve all events
		while (isset($location->parent->guid)) {
			$ancestors[] = $location->parent;
			$location = $location->parent;
		}
		if (!empty($ancestors)) {
			$calendar->events = array_merge($calendar->events,
				$pines->entity_manager->get_entities(
					array('class' => com_hrm_event),
					array('&',
						'tag' => array('com_hrm', 'event'),
						'data' => array('private', false)),
					array('!&', 'isset' => array('employee')),
					array('|', 'ref' => array('group', $ancestors))
				)
			);
		}
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