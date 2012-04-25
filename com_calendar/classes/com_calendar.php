<?php
/**
 * com_calendar class.
 *
 * @package Components
 * @subpackage calendar
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
 * @package Components
 * @subpackage calendar
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
	 * Return the agenda widget.
	 * @param string $position The module's position.
	 * @param int $order The module's order.
	 * @param array $options The module's options.
	 * @return module The module.
	 */
	public function agenda_widget($position = null, $order = null, $options = null) {
		$module = new module('com_calendar', 'show_calendar', $position, $order);
		$module->is_widget = true;
		foreach ($options as $key => $value)
			$module->$key = $value;

		return $module;
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
	 * @param bool $descendants Whether to show descendant locations.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null, $descendants = false) {
		global $pines;
		$pines->page->override = true;

		if (!isset($location))
			$location = $_SESSION['user']->group->guid;
		$module = new module('com_calendar', 'form_location');
		$module->location = $location;
		$module->descendants = $descendants;

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
	 * @param string $timezone The timezone the calendar is to use.
	 * @param group $location The desired location to view the schedule for.
	 * @param com_hrm_employee $employee The desired employee to view the schedule for.
	 * @param bool $descendants Whether to show descendant locations.
	 * @param string $filter Which type of events to show.
	 */
	public function show_calendar($view_type, $start, $end, $timezone, $location = null, $employee = null, $descendants = false, $filter = 'all') {
		global $pines;

		// Make all calculations in the correct timezone.
		$cur_timezone = date_default_timezone_get();
		date_default_timezone_set($timezone);

		if (!isset($location) || !isset($location->guid)) {
			$location = $_SESSION['user']->group;
			if (!isset($employee->guid))
				$employee = $_SESSION['user'];
		}

		$calendar_head = new module('com_calendar', 'show_calendar_head', 'head');
		$calendar = new module('com_calendar', 'show_calendar', 'content');
		$form = new module('com_calendar', 'form_calendar', 'right');

		// Datespan of the calendar.
		$date_start = strtotime('00:00:00', $start);
		$date_end = strtotime('11:59:59', $end) + 1;
		$calendar->view_type = $form->view_type = $view_type;
		$calendar->timezone = $form->timezone = $timezone;
		$calendar->date[0] = $form->date[0] = $date_start;
		$calendar->date[1] = $form->date[1] = $date_end;
		if (isset($employee->guid))
			$form->employee = $calendar->employee = $employee;

		$form->employees = $pines->com_hrm->get_employees();
		$calendar->location = $form->location = $location;
		$calendar->descendants = $form->descendants = $descendants;
		$calendar->filter = $form->filter = $filter;

		date_default_timezone_set($cur_timezone);

		// So the form can access the calendar.
		$form->cal_muid = $calendar->muid;
	}

	/**
	 * Get calendar events.
	 * 
	 * @param int $start The start date of the events.
	 * @param int $end The end date of the events.
	 * @param string $timezone The timezone to use.
	 * @param group $location The desired location to get events for.
	 * @param com_hrm_employee $employee The desired employee to get events for.
	 * @param bool $descendants Whether to use descendant locations.
	 * @param string $filter Which type of events to get.
	 * @return array An array of calendar events.
	 */
	public function get_events($start, $end, $timezone, $location = null, $employee = null, $descendants = false, $filter = 'all') {
		global $pines;

		// Make all calculations in the correct timezone.
		$cur_timezone = date_default_timezone_get();
		date_default_timezone_set($timezone);

		if (!isset($location) || !isset($location->guid)) {
			$location = $_SESSION['user']->group;
			if (!isset($employee->guid))
				$employee = $_SESSION['user'];
		}

		$selector = array('&', 'tag' => array('com_calendar', 'event'));
		// Datespan of the calendar.
		$selector['lt'] = array('start', $end);
		$selector['gte'] = array('end', $start);
		// Filters for the calendar event types.
		if ($filter == 'shifts') {
			$selector['isset'] = array('scheduled');
		} elseif ($filter == 'appointments') {
			$selector['isset'] = array('appointment');
		}
		if ($descendants)
			$or = array('|', 'ref' => array('group', $location->get_descendants(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$ancestors = array();
		if (isset($employee->guid)) {
			$or = array('|', 'isset' => array('group'));
			$selector['ref'] = array('employee', $employee);
			$ancestors = $location->get_descendants(true);
		}

		//Retrieve all private events
		if ($filter == 'events') {
			$events = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					$selector,
					$or,
					array('!&',
						'isset' => array('scheduled', 'appointment')
					)
				);
		} else {
			$events = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					$selector,
					$or
				);
		}
		// Retrieve all public events
		while (isset($location->parent->guid)) {
			$ancestors[] = $location->parent;
			$location = $location->parent;
		}
		if (!empty($ancestors)) {
			unset($selector['ref']);
			$selector['data'] = array('private', false);
			$more_events = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					$selector,
					array('!&',
						'isset' => array('employee')
					),
					array('|',
						'ref' => array('group', $ancestors)
					)
				);
			foreach ($more_events as $cur_more_event) {
				if (!$cur_more_event->in_array($events))
					$events[] = $cur_more_event;
			}
		}

		date_default_timezone_set($cur_timezone);

		return $events;
	}
}

?>