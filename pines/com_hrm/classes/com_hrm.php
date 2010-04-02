<?php
/**
 * com_hrm class.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
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
		$calendar_events = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'event'), 'class' => com_hrm_event));
		foreach ($calendar_events as $cur_event)
			$cur_event->delete();
	}

	/**
	 * Creates and attaches a module which lists employees.
	 */
	function list_employees() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_hrm', 'list_employees', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/list_employees'];

		$module->employees = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));

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

		$pines->com_pgrid->load();

		$module = new module('com_hrm', 'list_timeclocks', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/list_timeclocks'];

		$module->employees = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));

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

		$pines->com_pgrid->load();

		$module = new module('com_hrm', 'list_user_templates', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/list_user_templates'];

		$module->user_templates = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'user_template'), 'class' => com_hrm_user_template));

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
		$employee = $pines->entity_manager->get_entity(array('ref' => array('user_account' => $_SESSION['user']), 'tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));
		if (is_null($employee))
			return null;
		return $employee->print_clockin();
	}

	/**
	 * Creates and attaches a module which shows the calendar.
	 */
	function show_calendar($id = null) {
		global $pines;
		if (gatekeeper('com_hrm/editcalendar')) {
			$form = new module('com_hrm', 'form_calendar', 'left');
			$form->employees = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));
			// If an id is specified, the event info will be displayed for editing.
			if (isset($id))
				$form->event = com_hrm_event::factory((int) $id);
		}
		$calendar_head = new module('com_hrm', 'show_calendar_head', 'head');
		$calendar = new module('com_hrm', 'show_calendar', 'content');
		$calendar->events = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'event'), 'class' => com_hrm_event));
	}
}

?>