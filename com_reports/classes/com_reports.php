<?php
/**
 * com_reports class.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_reports main class.
 *
 * @package Pines
 * @subpackage com_reports
 */
class com_reports extends component {
	/**
	 * Creates and attaches a module which reports sales.
	 *
	 * @param int $start The start date of the report.
	 * @param int $end The end date of the report.
	 * @param group $location The group to report on.
	 * @param int $employee The employee to report on.
	 * @return module The attendance report module.
	 */
	function report_attendance($start, $end, $location = null, $employee = null) {
		global $pines;
		$date_start = strtotime('00:00', $start);
		$date_end = strtotime('23:59', $end);

		$form = new module('com_reports', 'form_hrm', 'right');
		$module = new module('com_reports', 'report_attendance', 'content');

		if (!isset($employee)) {
			$module->employees = $pines->entity_manager->get_entities(array('class' => com_hrm_employee), array('&', 'tag' => array('com_hrm', 'employee')));
			foreach ($module->employees as $key => &$cur_employee) {
				if (!$cur_employee->user_account || !($cur_employee->user_account->in_group($location) || $cur_employee->user_account->is_descendent($location)))
					unset($module->employees[$key]);
			}
		} else {
			$module->employee = $form->employee = $employee;
		}
		$module->date[0] = $form->date[0] = $date_start;
		$module->date[1] = $form->date[1] = $date_end;
		$module->location = $form->location = $location;
	}

	/**
	 * Creates and attaches a module which reports sales.
	 * 
	 * @param int $start The start date of the report.
	 * @param int $end The end date of the report.
	 * @param group $location The location to report sales for.
	 * @param employee $employee The employee to report sales for.
	 * @return module The sales report module.
	 */
	function report_sales($start, $end, $location = null, $employee = null) {
		global $pines;

		$form = new module('com_reports', 'form_sales', 'right');
		$head = new module('com_reports', 'show_calendar_head', 'head');
		$module = new module('com_reports', 'report_sales', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		$date_start = strtotime('00:00', $start);
		$date_end = strtotime('23:59', $end);
		$selector['gte'] = array('p_cdate', $date_start);
		$selector['lte'] = array('p_cdate', $date_end);
		$module->date[0] = $form->date[0] = $date_start;
		$module->date[1] = $form->date[1] = $date_end;
		// Employee and location of the report.
		if (isset($employee->user_account)) {
			$selector['ref'] = array('user', $employee->user_account);
			$module->employee = $form->employee = $employee;
			$module->title = 'Sales Report for '.$employee->name;
		} elseif (isset($location->guid)) {
			$selector['ref'] = array('group', $location);
			$module->title = 'Sales Report for '.$location->name;
		} else {
			$location = $_SESSION['user']->group;
			$module->all = true;
			$module->title = 'Sales Report for All Locations';
		}
		$module->location = $form->location = $location->guid;
		$form->employees = $pines->entity_manager->get_entities(array('class' => com_hrm_employee), array('&', 'tag' => array('com_hrm', 'employee')));
		$module->sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector);
	}

	/**
	 * Creates and attaches a module which lists sales rankings.
	 *
	 * @return module The sales report module.
	 */
	function list_sales_rankings() {
		global $pines;

		$module = new module('com_reports', 'list_sales_rankings', 'content');
		$module->rankings = $pines->entity_manager->get_entities(array('class' => com_reports_sales_ranking), array('&', 'tag' => array('com_reports', 'sales_ranking')));
	}
}

?>