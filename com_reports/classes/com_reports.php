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

		$form = new module('com_reports', 'form_hrm', 'left');
		$module = new module('com_reports', 'report_attendance', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_attendance'];

		if (!isset($employee)) {
			$module->employees = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));
			foreach ($module->employees as $key => &$cur_employee) {
				if (!$cur_employee->user_account || !($cur_employee->user_account->in_group($location) || $cur_employee->user_account->is_descendent($location)))
					unset($module->employees[$key]);
			}
		} else {
			$module->employee = $employee;
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
	 * @return module The sales report module.
	 */
	function report_sales($start, $end) {
		global $pines;
		$date_start = strtotime('00:00', $start);
		$date_end = strtotime('23:59', $end);
		
		$form = new module('com_reports', 'form_sales', 'left');
		$head = new module('com_hrm', 'show_calendar_head', 'head');
		$module = new module('com_reports', 'report_sales', 'content');
		$module->sales = $pines->entity_manager->get_entities(array('gte' => array('p_cdate' => $date_start), 'lte' => array('p_cdate' => $date_end), 'tags' => array('com_sales', 'sale'), 'class' => com_sales_sale));

		$module->date[0] = $form->date[0] = $date_start;
		$module->date[1] = $form->date[1] = $date_end;
	}
}

?>