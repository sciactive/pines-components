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
	 *
	 */
	private $user_templates;

	/**
	 * Check whether com_sales is installed and we should integrate with it.
	 *
	 * Places the result in $this->com_sales.
	 */
	function __construct() {
		global $pines;
		$this->com_sales = $pines->depend->check('component', 'com_sales');
	}

	/**
	 * Clears all events from the calendar.
	 */
	function clear_calendar() {
		global $pines;
		$calendar_events = $pines->entity_manager->get_entities(array('class' => com_hrm_event), array('&', 'tag' => array('com_hrm', 'event')));
		foreach ($calendar_events as $cur_event)
			$cur_event->delete();
	}

	/**
	 * Creates and attaches a module which lists employees.
	 */
	function list_employees() {
		global $pines;

		$module = new module('com_hrm', 'list_employees', 'content');

		$module->employees = $pines->entity_manager->get_entities(array('class' => com_hrm_employee), array('&', 'tag' => array('com_hrm', 'employee')));

		if ( empty($module->employees) ) {
			//$module->detach();
			pines_notice('There are no employees.');
		}
	}

	/**
	 * Creates and attaches a module which lists employees' timeclocks.
	 */
	function list_timeclocks() {
		global $pines;

		$module = new module('com_hrm', 'list_timeclocks', 'content');

		$module->employees = $pines->entity_manager->get_entities(array('class' => com_hrm_employee), array('&', 'tag' => array('com_hrm', 'employee')));

		if ( empty($module->employees) ) {
			//$module->detach();
			pines_notice('There are no employees.');
		}
	}

	/**
	 * Creates and attaches a module which lists user templates.
	 */
	function list_user_templates() {
		global $pines;

		$module = new module('com_hrm', 'list_user_templates', 'content');

		$module->user_templates = $pines->entity_manager->get_entities(array('class' => com_hrm_user_template), array('&', 'tag' => array('com_hrm', 'user_template')));

		if ( empty($module->user_templates) ) {
			//$module->detach();
			pines_notice('There are no user templates.');
		}
	}

	/**
	 * Transform a string to title case.
	 *
	 * @param string $string The string to transform.
	 * @param array $delimiters An array of strings used to delimit parts (words) of the string.
	 * @param array $exceptions An array of words which should not be changed.
	 * @return string The transformed string.
	 */
	function title_case($string, $delimiters = array(' ', '-', 'O\'', 'Mc'), $exceptions = array('to', 'a', 'the', 'of', 'by', 'and', 'with', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X')) {
		/*
		* Exceptions in lower case are words you don't want converted
		* Exceptions all in upper case are any words you don't want converted to title case
		*   but should be converted to upper case, e.g.:
		*   king henry viii or king henry Viii should be King Henry VIII
		*/
		foreach ($delimiters as $delimiter){
			$words = explode($delimiter, $string);
			$newwords = array();
			foreach ($words as $word){
				if (in_array(strtoupper($word), $exceptions)){
				// check exceptions list for any words that should be in upper case
					$word = strtoupper($word);
				} elseif (!in_array($word, $exceptions)){
				// convert to uppercase
					$word = ucfirst($word);
				}
				array_push($newwords, $word);
			}
			$string = join($delimiter, $newwords);
		}
		return $string;
	}

	/**
	 * Print a form for the current user to clockin, if they're allowed.
	 * @return module|null The form's module or null.
	 */
	function provide_clockin() {
		if (empty($_SESSION['user']) || !gatekeeper('com_hrm/clock'))
			return null;
		global $pines;
		$employee = $pines->entity_manager->get_entity(array('class' => com_hrm_employee), array('&', 'ref' => array('user_account', $_SESSION['user']), 'tag' => array('com_hrm', 'employee')));
		if (!isset($employee))
			return null;
		return $employee->print_clockin();
	}

	/**
	 * Creates and attaches a module which shows the calendar.
	 * @param int $id An event GUID.
	 * @param group $location The desired location to view the schedule for.
	 */
	function show_calendar($id = null, $location = null) {
		global $pines;
		
		if (!isset($location) || !isset($location->guid))
			$location = $_SESSION['user']->group;
		
		if (gatekeeper('com_hrm/editcalendar')) {
			$form_group = new module('com_hrm', 'form_calendar_groups', 'left');
			$form_event = new module('com_hrm', 'form_calendar', 'left');
			// If an id is specified, the event info will be displayed for editing.
			if (isset($id) && $id >  0) {
				$form_event->event = com_hrm_event::factory((int) $id);
				$location = $form_event->event->group;
			}
			// Should work like this, we need to have the employee's group update upon saving it to a user.
			$form_event->employees = $pines->entity_manager->get_entities(array('class' => com_hrm_employee), array('&', 'tag' => array('com_hrm', 'employee')));
			$form_group->location = $form_event->location = $location->guid;
		}
		$calendar_head = new module('com_hrm', 'show_calendar_head', 'head');
		$calendar = new module('com_hrm', 'show_calendar', 'content');
		$calendar->events = $pines->entity_manager->get_entities(array('class' => com_hrm_event), array('&', 'ref' => array('group', $location), 'tag' => array('com_hrm', 'event')));
		$calendar->location = $location->guid;
	}
}

?>