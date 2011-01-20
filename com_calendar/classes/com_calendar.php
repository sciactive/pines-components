<?php
/**
 * com_calendar class.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_calendar main class.
 *
 * Provides a company calendar.
 *
 * @package Pines
 * @subpackage com_calendar
 */
class com_calendar extends component {
	/**
	 * Whether to integrate with com_customer.
	 *
	 * @var bool $com_customer
	 */
	var $com_customer;

	/**
	 * Check whether com_customer is installed and we should integrate with it.
	 *
	 * Places the result in $this->com_customer.
	 */
	public function __construct() {
		global $pines;
		$this->com_customer = $pines->depend->check('component', 'com_customer');
	}

	/**
	 * Clears all events from the calendar.
	 */
	public function clear_calendar() {
		global $pines;
		$calendar_events = $pines->entity_manager->get_entities(array('class' => com_calendar_event), array('&', 'tag' => array('com_calendar', 'event')));
		foreach ($calendar_events as $cur_event)
			$cur_event->delete();
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

		$module = new module('com_calendar', 'form_lineup', 'content');
		$module->location = $location;
		$module->employees = $pines->com_hrm->get_employees();

		$pines->page->override_doc($module->render());
	}

	/**
	 * Print a form to select a company location.
	 *
	 * @param int $location The current location.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null, $descendents = false) {
		global $pines;
		$pines->page->override = true;

		if (!isset($location))
			$location = $_SESSION['user']->group->guid;
		$module = new module('com_calendar', 'form_location');
		$module->location = $location;
		$module->descendents = $descendents;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Print a form to edit the work schedule for an employee.
	 * @param com_hrm_employee $employee The employee to schedule.
	 */
	public function schedule_form($employee) {
		global $pines;
		$pines->page->override = true;
		$module = new module('com_calendar', 'form_schedule', 'content');
		$module->entity = $employee;
		$pines->page->override_doc($module->render());
	}

	/**
	 * Creates and attaches a module which shows the calendar.
	 * @param group $location The desired location to view the schedule for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @param com_hrm_employee $employee The desired employee to view the schedule for.
	 */
	public function show_calendar($location = null, $employee = null, $descendents = false) {
		global $pines;

		if (!isset($location) || !isset($location->guid)) {
			$location = $_SESSION['user']->group;
			if (!isset($employee->guid))
				$employee = $_SESSION['user'];
		}

		$calendar_head = new module('com_calendar', 'show_calendar_head', 'head');
		$calendar = new module('com_calendar', 'show_calendar', 'content');
		$form = new module('com_calendar', 'form_calendar', 'right');

		$selector = array('&', 'tag' => array('com_calendar', 'event'));
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$ancestors = array();
		if (isset($employee->guid)) {
			unset($selector['ref']);
			$selector['ref'][] = array('employee', $employee);
			$form->employee = $calendar->employee = $employee;
			$location = $employee->group;
			$ancestors = $location->get_descendents(true);
		}

		// Should work like this, we need to have the employee's group update upon saving it to a user.
		$form->employees = $pines->com_hrm->get_employees();
		$calendar->location = $form->location = $location;
		$form->descendents = $descendents;
		$calendar->events = $pines->entity_manager->get_entities(array('class' => com_calendar_event), $selector, $or);
		// Retrieve all events
		while (isset($location->parent->guid)) {
			$ancestors[] = $location->parent;
			$location = $location->parent;
		}
		if (!empty($ancestors)) {
			$calendar->events = array_merge($calendar->events,
				$pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					array('&',
						'tag' => array('com_calendar', 'event'),
						'data' => array('private', false)),
					array('!&', 'isset' => array('employee')),
					array('|', 'ref' => array('group', $ancestors))
				)
			);
		}
	}
}

?>