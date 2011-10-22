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
/* @var $pines pines */
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
	 * @param string $view_type The view frame of the calendar.
	 * @param int $start The start date of the calendar.
	 * @param int $end The end date of the calendar.
	 * @param group $location The desired location to view the schedule for.
	 * @param com_hrm_employee $employee The desired employee to view the schedule for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @param string $filter Which type of events to show.
	 */
	public function show_calendar($view_type, $start, $end, $location = null, $employee = null, $descendents = false, $filter = 'all') {
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
		// Datespan of the calendar.
		$date_start = strtotime('00:00:00', $start);
		$date_end = strtotime('00:00:00', $end) + 1;
		$selector['gte'] = array('start', $date_start);
		$selector['lt'] = array('end', $date_end);
		$calendar->view_type = $form->view_type = $view_type;
		$calendar->date[0] = $form->date[0] = $date_start;
		$calendar->date[1] = $form->date[1] = $date_end;
		// Filters for the calendar event types.
		if ($filter == 'shifts') {
			$selector['isset'] = array('scheduled');
		} elseif ($filter == 'appointments') {
			$selector['isset'] = array('appointment');
		}
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$ancestors = array();
		if (isset($employee->guid)) {
			$or = array('|', 'isset' => array('group'));
			$selector['ref'] = array('employee', $employee);
			$form->employee = $calendar->employee = $employee;
			$ancestors = $location->get_descendents(true);
		}

		$form->employees = $pines->com_hrm->get_employees();
		$calendar->location = $form->location = $location;
		$calendar->descendents = $form->descendents = $descendents;
		$calendar->filter = $form->filter = $filter;
		//Retrieve all private events
		if ($filter == 'events') {
			$calendar->events = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					$selector,
					$or,
					array('!&', 'isset' => array(array('scheduled'), array('appointment')))
				);
		} else {
			$calendar->events = $pines->entity_manager->get_entities(array('class' => com_calendar_event), $selector, $or);
		}
		// Retrieve all public events
		while (isset($location->parent->guid)) {
			$ancestors[] = $location->parent;
			$location = $location->parent;
		}
		if (!empty($ancestors)) {
			unset($selector['ref']);
			$selector['data'] = array('private', false);
			$calendar->events = array_merge($calendar->events,
				$pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					$selector,
					array('!&', 'isset' => array('employee')),
					array('|', 'ref' => array('group', $ancestors))
				)
			);
		}
	}
}

?>