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
	 * Print a form to select date timespan.
	 *
	 * @param bool $all_time Currently searching all records or a timespan.
	 * @param string $start The current starting date of the timespan.
	 * @param string $end The current ending date of the timespan.
	 * @return module The form's module.
	 */
	public function date_select_form($all_time = false, $start = null, $end = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_reports', 'date_selector', 'content');
		$module->all_time = $all_time;
		$module->start_date = $start;
		$module->end_date = $end;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Creates and attaches a module which lists company paystubs.
	 *
	 * @return module The payroll list module.
	 */
	function list_paystubs() {
		global $pines;

		$module = new module('com_reports', 'list_paystubs', 'content');
		$module->paystubs = $pines->entity_manager->get_entities(array('class' => com_reports_paystub), array('&', 'tag' => array('com_reports', 'paystub')));

		if ( empty($module->paystubs) )
			pines_notice('There are no completed paystubs to view.');
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

	/**
	 * Print a form to select a location.
	 *
	 * @param int $location The currently set location to search in.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null, $descendents = false) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_reports', 'location_selector', 'content');
		if (!isset($location)) {
			$module->location = $_SESSION['user']->group->guid;
		} else {
			$module->location = $location;
		}
		$module->descendents = $descendents;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Creates and attaches a module which summarizes employee totals.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The employee summary module.
	 */
	function employee_summary($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'employee_summary', 'content');

		$selector = array('&');
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$selector['tag'] = array('com_sales', 'sale');
		$selector['strict'] = array('status', 'paid');
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$selector['strict'] = array('status', 'processed');
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->invoices = array_merge($sales, $returns);

		return $module;
	}

	/**
	 * Creates and attaches a module which summarizes all sales, returns and voids.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module the invoice summary module.
	 */
	function invoice_summary($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'invoice_summary', 'content');

		$selector = array('&');
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$selector['tag'] = array('com_sales', 'sale');
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->invoices = array_merge($sales, $returns);

		return $module;
	}

	/**
	 * Creates and attaches a module which summarizes locational totals.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The location summary module.
	 */
	function location_summary($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'location_summary', 'content');

		$selector = array('&');
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$selector['tag'] = array('com_sales', 'sale');
		$selector['strict'] = array('status', 'paid');
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$selector['strict'] = array('status', 'processed');
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->invoices = array_merge($sales, $returns);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports sales.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param int $employee The employee to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The attendance report module.
	 */
	function report_attendance($start_date = null, $end_date = null, $location = null, $employee = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_attendance', 'content');
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if (!isset($employee)) {
			$module->employees = $pines->com_hrm->get_employees(true);
			foreach ($module->employees as $key => &$cur_employee) {
				if (!($cur_employee->in_group($location) || ($descendents && $cur_employee->is_descendent($location))))
					unset($module->employees[$key]);
			}
		} else {
			$module->employee = $employee;
		}
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->location = $location;
		$module->descendents = $descendents;
	}

	/**
	 * Creates and attaches a module which reports calendar events.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The calendar report module.
	 */
	function report_calendar($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_calendar', 'content');

		$selector = array('&', 'tag' => array('com_calendar', 'event'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('start', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('end', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->events = $pines->entity_manager->get_entities(array('class' => com_calendar_event), $selector, $or);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports an employee's payroll.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param entity $employee The enitity of the employee
	 * @param long $payperhour this is passed in from report_payroll_summary
	 * @param long $totalhours this is the total amount of hours work
	 * @param long $totalpay this is the total they're being paid for the time period
	 * @param long $hours hours worked
	 * @param long $salary this is the pay for the time period of salaried employees
 	 * @param long $commission this is the total commission
	 * @return module the payroll summary module.
	 */
	function report_individual_payroll($start_date = null, $end_date = null,  $employee = null, $payperhour = null, $totalhours =null, $totalpay = null,$salary = null, $commission=null) {
		global $pines;
		$module = new module('com_reports', 'report_individual_payroll', 'content');
		$module->employee = $employee;
		$module->pay_per_hour = $payperhour;
		$module->total_hours = $totalhours;
		$module->total_pay = $totalpay;
		$module->salary = $salary;
		$module->commission=$commission;
		$time_diff_weeks_hours = (($end_date - $start_date)*(1/604800))*40;
		if((string)$totalhours > (string)$time_diff_weeks_hours){
			$module->reg_hours = $time_diff_weeks_hours;
			$module->overtime = $totalhours - $time_diff_weeks_hours;
		}else{
			$module->reg_hours = $totalhours;
			$module->overtime = 0;
		}

		$selector = array('&');
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date -1;
		$module->all_time = (!isset($start_date) && !isset($end_date));		
		$and= array('&', 'ref' => array('user', $module->employee));
		$selector['tag'] = array('com_sales', 'sale');
		$module->sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $and);
		$selector['tag'] = array('com_sales', 'return');
		// Make sure this sale is not attached to any returns.
		foreach ($module->sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
					array('class' => com_sales_return, 'skip_ac' => true),
					array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $cur_sale))
				);
			if (isset($return->guid))
				unset($module->sales[$key]);
		}
		unset($cur_sale);
		return $module;
	}
	/**
	 * Creates and attaches a module which reports employee issues.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The issues report module.
	 */
	function report_issues($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_issues', 'content');

		$selector = array('&', 'tag' => array('com_hrm', 'issue'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->issues = $pines->entity_manager->get_entities(array('class' => com_hrm_issue), $selector, $or);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports MiFi Sales.
	 *
	 * @param int $verbose Whether to show extraneous application information.
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The MiFi report module.
	 */
	function report_mifi($verbose = false, $start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_mifi', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$selector['strict'] = array('status', 'paid');
		//$selector['match'] = array('payments', 'MiFi Finance');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->verbose = $verbose;
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		// Make sure this sale is not attached to any returns.
		foreach ($module->sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
				array('class' => com_sales_return, 'skip_ac' => true),
				array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $cur_sale))
			);
			if (isset($return->guid))
				unset($module->sales[$key]);
		}
		unset($cur_sale);
		return $module;
	}

	/**
	 * Creates and attaches a module which reports MiFi Sales.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The MiFi report module.
	 */
	function report_mifi_sales($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_mifi_sales', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$selector['strict'] = array('status', 'paid');
		//$selector['match'] = array('payments', 'MiFi Finance');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		// Make sure this sale is not attached to any returns.
		foreach ($module->sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
					array('class' => com_sales_return, 'skip_ac' => true),
					array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $cur_sale))
				);
			if (isset($return->guid))
				unset($module->sales[$key]);
		}
		unset($cur_sale);
		return $module;
	}

	/**
	 * Creates and attaches a module which reports MiFi Sales per user/employee.
	 *
	 * //param int $verbose Whether to show extraneous application information.
	 * 
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The MiFi report module.
	 */
	function report_mifi_employee_app_totals($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;
		$module = new module('com_reports', 'report_mifi_employee_app_totals', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$selector['strict'] = array('status', 'paid');
		//$selector['match'] = array('payments', 'MiFi Finance');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// What's this?
		//$module->verbose = $verbose;
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		// Make sure this sale is not attached to any returns.
		foreach ($sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
				array('class' => com_sales_return, 'skip_ac' => true),
				array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $cur_sale))
			);
			if (isset($return->guid))
				unset($sales[$key]);
		}
		unset($cur_sale);

		// This gets totals of individual users totals.
		$module->sales_totals = array();

		foreach ($sales as $cur_sale){
			$application = $pines->entity_manager->get_entity(
					array('class' => com_mifi_application),
					array('&',
						'tag' => array('com_mifi', 'application'),
						'ref' => array('customer', $cur_sale->customer)
					)
				);
			$contract = $pines->entity_manager->get_entity(
					array('class' => com_mifi_contract),
					array('&',
						'tag' => array('com_mifi', 'contract'),
						'ref' => array('sale', $cur_sale)
					)
				);

			if (!isset($module->sales_totals[$cur_sale->user->guid])) {
				$module->sales_totals[$cur_sale->user->guid] = array(
					'name' => $cur_sale->user->name,
					'type' => $cur_sale->user->employee ? 'Employee' : ($cur_sale->user->has_tag('customer') ? 'Customer' : 'User'),
					'sales' => 0,
					'user_guid' => $cur_sale->user->guid
				);

				/*
				$sales_totals[$cur_sale->user->guid]['group_name'] = $cur_sale->group->name;
				$sales_totals[$cur_sale->user->guid]['group_guid'] = $cur_sale->group->guid;
				*/
			}
			$module->sales_totals[$cur_sale->user->guid]['sales']++;

			// This is determing count for valid contracts.
			if (isset($contract->status) && $contract->status != 'voided' && $contract->status != 'returned') {
				$module->sales_totals[$cur_sale->user->guid]['valid']++;
			}

			// Adds employer applications.
			if (isset($application->active_status)) {
				$module->sales_totals[$cur_sale->user->guid][$application->active_status]++;
			} else {
				$module->sales_totals[$cur_sale->user->guid]['null']++;
			}
		}

		return $module;
	}

	/**
	 * Creates and attaches a module which reports MiFi Sales per location.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The MiFi report module.
	 */
	function report_mifi_location_app_totals($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;
		$module = new module('com_reports', 'report_mifi_location_app_totals', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$selector['strict'] = array('status', 'paid');
		//$selector['match'] = array('payments', 'MiFi Finance');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		// Make sure this sale is not attached to any returns.
		foreach ($sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
				array('class' => com_sales_return, 'skip_ac' => true),
				array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $cur_sale))
			);
			if (isset($return->guid))
				unset($sales[$key]);
		}
		unset($cur_sale);

		// This gets totals of individual locations and gets their total apps.
		$module->location_total = array();

		foreach ($sales as $cur_sale) {
			// Getting applicaton.
			$application = $pines->entity_manager->get_entity(
					array('class' => com_mifi_application),
					array('&',
						'tag' => array('com_mifi', 'application'),
						'ref' => array('customer', $cur_sale->customer)
					)
				);
			// Getting contract if there is one.
			$contract = $pines->entity_manager->get_entity(
					array('class' => com_mifi_contract),
					array('&',
						'tag' => array('com_mifi', 'contract'),
						'ref' => array('sale', $cur_sale)
					)
				);

			if (!isset($module->location_total[$cur_sale->group->guid])) {
				$module->location_total[$cur_sale->group->guid] = array(
					'name' => $cur_sale->group->name,
					'sales' => 0,
					'location_guid' => $cur_sale->group->guid
				);
			}
			$module->location_total[$cur_sale->group->guid]['sales']++;

			// This is to determine valid contracts resulting from applications.
			if (isset($contract->status) && $contract-status != 'voided' && $contract->status != 'returned') {
				$module->location_total[$cur_sale->group->guid]['valid']++;
			}
			// This was added for determing employer on application.
			if (isset($application->active_status)) {
				$module->location_total[$cur_sale->group->guid][$application->active_status]++;
			} else {
				$module->location_total[$cur_sale->group->guid]['null']++;
			}
		}

		return $module;
	}

	/**
	 * Creates and attaches a module which shows customers with MiFi Financing.
	 *
	 * @return module The MiFi report module.
	 */
	function report_mifi_available() {
		global $pines;

		$module = new module('com_reports', 'report_mifi_available', 'content');

		// Select any MiFi applications with existing financing approved
		$groups = $_SESSION['user']->group->get_descendents(true);
		$module->applications = $pines->entity_manager->get_entities(
				array('class' => com_mifi_application, 'skip_ac' => true),
				array('&',
					'tag' => array('com_mifi', 'application'),
					'gte' => array('approval_amount', 1500),
					'lte' => array('p_cdate', strtotime('-30 days'))
				),
				array('|', 'ref' => array('group', $groups))
			);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports MiFi Contracts.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The MiFi report module.
	 */
	function report_mifi_contracts($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_mifi_contracts', 'content');

		$selector = array('&',
				'tag' => array('com_mifi', 'contract'),
				'strict' => array('status', 'tendered')
			);
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		//$selector['match'] = array('payments', 'MiFi');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->contracts = $pines->entity_manager->get_entities(array('class' => com_mifi_contract), $selector, $or);

		return $module;
	}

	/**
	 * Creates and attaches a module which shows customers with MiFi Faxsheets.
	 *
	 * @return module The MiFi report module.
	 */
	function report_mifi_faxsheets() {
		global $pines;

		$module = new module('com_reports', 'report_mifi_faxsheets', 'content');

		// Select any MiFi contracts with faxsheets requests.
		$groups = $_SESSION['user']->group->get_descendents(true);
		$module->contracts = $pines->entity_manager->get_entities(
				array('class' => com_mifi_contract, 'skip_ac' => true),
				array('&',
					'tag' => array('com_mifi', 'contract'),
					'isset' => array('faxsheet_request'),
					'strict' => array('status', 'tendered')
				),
				array('!&',
					'strict' => array('archived', true)
				)
			);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports employee payroll information.
	 *
	 * @param bool $entire_company Whether or not to show the entire company.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The employee payroll module.
	 */
	function report_payroll($entire_company = true, $location = null, $descendents = false) {
		global $pines;

		$pay_start = strtotime($pines->config->com_hrm->pay_start);
		$pay_period = $pines->config->com_hrm->pay_period;
		$total_time = (strtotime('0:00:00') - $pay_start)/$pay_period;
		// Start date is 1 day after the last pay period.
		$start_date = $pay_start + (floor($total_time) * $pay_period) + 86400;
		if (floor($total_time) == $total_time)
			$start_date = strtotime('-1 week', $start_date);
		// End date is at the end of the last day of the pay period.
		$end_date = $pay_start + (ceil($total_time) * $pay_period) + 86399;

		$paystub = $pines->entity_manager->get_entity(array('class' => com_reports_paystub),
				array('&',
					'tag' => array('com_reports', 'paystub'),
					'gte' => array('end', (int) $start_date)
				)
			);
		if (isset($paystub->guid))
			return $paystub->show($entire_company, $location, $descendents);

		$module = new module('com_reports', 'report_payroll', 'content');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->entire_company = $entire_company;
		$module->employees = $pines->com_hrm->get_employees(true);
		$or = array();
		if (!$module->entire_company) {
			// Location of the report.
			if (!isset($location->guid))
				$location = $_SESSION['user']->group;
			if ($descendents)
				$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
			else
				$or = array('|', 'ref' => array('group', $location));
			$module->location = $location;
			$module->descendents = $descendents;
			foreach ($module->employees as $key => &$cur_employee) {
				if (!($cur_employee->in_group($location) || ($descendents && $cur_employee->is_descendent($location))))
					unset($module->employees[$key]);
			}
		}
		// Sales
		$selector = array('&',
				'tag' => array('com_sales', 'sale'),
				'data' => array('status', 'paid'),
				'gte' => array('p_cdate', (int) $module->start_date),
				'lt' => array('p_cdate', (int) $module->end_date)
			);
		if (!empty($or))
			$selector = array_merge($selector, $or);
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector);
		// Returns
		$selector = array('&',
				'tag' => array('com_sales', 'return'),
				'data' => array('status', 'processed'),
				'gte' => array('p_cdate', (int) $module->start_date),
				'lt' => array('p_cdate', (int) $module->end_date)
			);
		if (!empty($or))
			$selector = array_merge($selector, $or);
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector);
		$module->invoices = array_merge($sales, $returns);

		return $module;
	}

	/**
	 * Creates and attaches a module which summarizes employee payroll.
	 * 
	 * Hourly employees pay is totaled for the hourly pay and then their commission pay.
	 * If their commission is greater than their hourly they get a status of commission.
	 * If their hourly is more they get a draw status.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module the invoice summary module.
	 */
	function report_payroll_summary($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

	
		$module = new module('com_reports', 'report_payroll_summary', 'content');

		$selector = array('&',
				'tag' => array('com_sales', 'sale')
			);

		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));

		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;

		$module->sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		// Make sure this sale is not attached to any returns.
		foreach ($module->sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
					array('class' => com_sales_return, 'skip_ac' => true),
					array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $cur_sale))
				);
			if (isset($return->guid))
				unset($module->sales[$key]);
		}
		unset($cur_sale);

		// Find employees in the location or its descendents.
		$employees = $pines->com_hrm->get_employees(true);
		$module->employees = array();
		foreach ($employees as $key => &$cur_employee) {
			if (!($cur_employee->in_group($location) || ($descendents && $cur_employee->is_descendent($location))))
				continue;
			$module->employees[] = array('entity' => $cur_employee);
		}
		unset($cur_empoloyee);

		// Calculate time totals based on scheduled vs clocked hours.
		$totals = array();
		$total_group['scheduled'] = $total_group['clocked'] = $time_punch = $total_count = 0;
		foreach ($module->employees as &$cur_employee) {
			$totals[$total_count]['scheduled'] = $totals[$total_count]['clocked'] = 0;
			$schedule = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					array('&',
						'tag' => array('com_calendar', 'event'),
						'gte' => array('start', $this->start_date),
						'lt' => array('end', $this->end_date),
						'ref' => array('employee', $cur_employee['entity'])
					)
				);
			foreach ($schedule as $cur_schedule)
				$totals[$total_count]['scheduled'] += $cur_schedule->scheduled;
			$totals[$total_count]['clocked'] = $cur_employee['entity']->timeclock->sum($start_date, $end_date);
			$cur_employee['scheduled'] = round($totals[$total_count]['scheduled'] / 3600, 2);
			$cur_employee['clocked'] = round($totals[$total_count]['clocked'] / 3600, 2);
			$cur_employee['variance'] = round(($totals[$total_count]['clocked'] - $totals[$total_count]['scheduled']) / 3600, 2);
		
			$total_group['scheduled'] += $totals[$total_count]['scheduled'];
			$total_group['clocked'] += $totals[$total_count]['clocked'];
			$total_count++;
		}
		unset($cur_empoloyee);
		$module->total_scheduled = $total_group['scheduled'];
		$module->total_clocked = $total_group['clocked'];

		// Determine each employee's sale totals.
		$saletotals = array();
		$numbersales= array();
		$commission_array = array();
		$total_num_sales = 0;
		foreach ($module->sales as $cur_sale) {
			$guid = $cur_sale->user->guid;	
			if (empty($saletotals[$guid])) {
				$saletotals[$guid] = $cur_sale->subtotal;
				$numbersales[$guid] = 1;
				$total_num_sales++;
				if ($cur_sale->products->commission)
					$commission_array[$guid] = number_format($cur_sale->products->commission, 2, '.', '');
				else
					$commission_array[$guid] = number_format(($cur_sale->subtotal * 0.06), 2, '.', '');
			} else {
				$saletotals[$guid] += $cur_sale->subtotal;
				$numbersales[$guid]++;
				$total_num_sales++;
				if ($cur_sale->products->commission)
					$commission_array[$guid] += number_format($cur_sale->products->commission, 2, '.', '');
				else
					$commission_array[$guid] += number_format(($cur_sale->subtotal * 0.06), 2, '.', '');
			}			
		}
		$module->group_num_sales = $total_num_sales;

		// Determine each store's sales totals.
		$module->group_sales_total = 0;
		foreach ($module->employees as &$cur_employee) {
			if (array_key_exists($cur_employee['entity']->guid, $saletotals)) {
				$cur_employee['sales_total'] = $saletotals[$cur_employee['entity']->guid];
				if ($cur_employee['entity']->pay_type != 'salary')
					$module->group_sales_total += $saletotals[$cur_employee['entity']->guid];
				$cur_employee['number_sales'] = $numbersales[$cur_employee['entity']->guid];
			} else {
				$cur_employee['sales_total'] = 0;
				$cur_employee['number_sales'] = 0;
			}
		}
		unset($cur_empoloyee);

		// More computing variables to use in view.
		$commission_percent = array(
			'draw' => 0,
			'commission' => 0
		);
		$module->group_salary_total = 0;
		$pay_rate = array(
			'rate' => 0,
			'people' => 0
		);
		$module->group_hours = 0;
		$module->commission_total = 0;
		$module->group_reg_hours = 0;
		$module->group_overtime_hours = 0;
		$module->group_weekly_total = 0;
		$module->group_pay_total = 0;
		$group_percent_rate = 0;

		foreach ($module->employees as &$cur_employee) {
			// Determines what the employees commision is for this time period
			// otherwise it get the default 6% commission.
			if (array_key_exists($cur_employee['entity']->guid, $commission_array))
				$cur_employee['commission'] = $commission_array[$cur_employee['entity']->guid];
			else
				$cur_employee['commission'] = 0.06 * $cur_employee['sales_total'];
			// Figure out what the time frame is the employee has worked in weeks.
			$time_diff_weeks = ($module->end_date - $module->start_date) / 604800;
			// Get the amount of hours for that amount of weeks, which won't be overtime hours.
			$weeks_hours = $time_diff_weeks * 40;
			// Get the amount of hours worked.
			$cur_employee['hour_total'] = $cur_employee['clocked'];
			// Figure out if they have any overtime.
			if ($cur_employee['hour_total'] > $weeks_hours) {
				$cur_employee['reghours'] = $cur_employee['entity']->pay_rate * $weeks_hours;
				$cur_employee['overtimehours'] = ($cur_employee['hour_total'] - $weeks_hours) * ($cur_employee['entity']->pay_rate * 1.5);
			} else {
				$cur_employee['reghours'] = ($cur_employee['entity']->pay_rate * $cur_employee['hour_total']);
				$cur_employee['overtimehours'] = 0;
			}
			// Add together their total pay for this time period and determine
			// if they're draw or commission.
			$cur_employee['hour_pay_total'] = $cur_employee['reghours'] + $cur_employee['overtimehours'];
			if (($cur_employee['hour_pay_total'] > $cur_employee['commission']) && $cur_employee['entity']->pay_type == 'commission_draw') {
				$cur_employee['commission_status'] = 'draw';
				$cur_employee['weekly'] = $cur_employee['hour_pay_total'] - $cur_employee['commission'];
				$cur_employee['pay_total'] = $cur_employee['hour_pay_total'];
			} elseif ($cur_employee['entity']->pay_type == 'commission_draw') {
				$cur_employee['commission_status'] = 'commission';
				$cur_employee['weekly'] = 0;
				$cur_employee['pay_total'] = $cur_employee['commission'];	
			} else {
				$cur_employee['pay_total'] = $cur_employee['hour_pay_total'] = $cur_employee['reghours'] + $cur_employee['overtimehours'];
				$cur_employee['commission_status'] = 'hourly';
				$cur_employee['commission'] = 0;
			}

			// Ensures no 0 amount for the total_rate.
			if ($cur_employee['clocked'] == 0)
				$cur_employee['total_rate'] = $cur_employee['pay_total'];
			else
				$cur_employee['total_rate'] = $cur_employee['pay_total'] / $cur_employee['clocked'];
			// Does computations for salaried employee variables.
			if ($cur_employee['entity']->pay_type == 'salary') {
				$cur_employee['commission_status'] = 'salary';
				if (isset($start_date)) {
					$ratio = (($end_date - $start_date) / 86400) / 360;
					$cur_employee['salary_pay_period'] = $cur_employee['entity']->pay_rate * $ratio;
					$module->group_salary_total += round($cur_employee['salary_pay_period'], 2);
					$cur_employee['pay_total'] = $cur_employee['salary_pay_period'];
				} else {
					$cur_employee['salary_pay_period'] = $cur_employee['entity']->pay_rate;
					$module->group_salary_total += round($cur_employee['salary_pay_period'], 2);
					$cur_employee['pay_total'] = $cur_employee['salary_pay_period'];
				}
			}	
			// Add the total amount of hourly employees on commission and the
			// total amount on draw.
			if ($cur_employee['commssion_status'] == 'draw')
				$commission_percent['draw']++;
			elseif (($cur_employee['commission_status'] != 'salary') && ($cur_employee['commission_status'] != 'hourly'))
				$commission_percent['commission']++;
			// Determine the totals for the totals entry line of the report.
			if ($cur_employee['commission_status'] != 'salary') {
				$pay_rate['rate'] += $cur_employee['entity']->pay_rate;
				$pay_rate['people']++;
				$module->group_hours += $cur_employee['clocked'];
				$module->commission_total += $cur_employee['commission'];
				$module->group_reg_hours += $cur_employee['reghours'];
				$module->group_overtime_hours += $cur_employee['overtimehours'];
				$module->group_weekly_total += $cur_employee['weekly'];
				$group_percent_rate += $cur_employee['total_rate'];
			}
			$module->group_pay_total += round($cur_employee['pay_total'], 2);	
		}
		unset($cur_empoloyee);

		if($pay_rate['people']!= 0) {
			$module->group_percent_rate = $group_percent_rate / $pay_rate['people'];
			$module->pay_rate_total = $pay_rate['rate'] / $pay_rate['people'];
			$module->commission_percent = $commission_percent['commission'] / $pay_rate['people'];
		} else {
			$pay_rate = 0;
			$module->pay_rate_total = 0;
			$module->group_percent_rate = 0;
		}
		// Get total sales.
		$module->group_sales = 0;
		foreach ($saletotals as $value) {
			$module->group_sales += $value;
		}

		return $module;
	}

	/**
	 * Creates and attaches a module which reports product details.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The location to report sales for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @param bool $types The types of transactions to show.
	 * @return module The product details report module.
	 */
	function report_product_details($start_date = null, $end_date = null, $location = null, $descendents = false, $types = null) {
		global $pines;

		$module = new module('com_reports', 'report_product_details', 'content');
		if (isset($types)) {
			$module->types = $types;
		} else {
			$module->types = array(
				'sold' => true,
				'invoiced' => true,
				'returned' => true,
				'voided' => true,
				'return' => true
			);
		}

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->transactions = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);

		if ($module->types['return']) {
			$selector['tag'] = array('com_sales', 'return');
			$selector['strict'] = array('status', 'processed');
			$module->transactions = array_merge($module->transactions, $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or));
		}
	}

	/**
	 * Creates and attaches a module which reports sales.
	 *
	 * @param int $start The start date of the report.
	 * @param int $end The end date of the report.
	 * @param group $location The location to report sales for.
	 * @param employee $employee The employee to report sales for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The sales report module.
	 */
	function report_sales($start, $end, $location = null, $employee = null, $descendents = false) {
		global $pines;

		$form = new module('com_reports', 'form_sales', 'right');
		$head = new module('com_reports', 'show_calendar_head', 'head');
		$module = new module('com_reports', 'report_sales', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'transaction', 'sale_tx'));
		$or = array();
		// Datespan of the report.
		$date_start = strtotime('00:00:00', $start);
		$date_end = strtotime('23:59:59', $end) + 1;
		$selector['gte'] = array('p_cdate', $date_start);
		$selector['lt'] = array('p_cdate', $date_end);
		$module->date[0] = $form->date[0] = $date_start;
		$module->date[1] = $form->date[1] = $date_end;
		// Employee and location of the report.
		if (isset($employee->guid)) {
			$selector['ref'] = array('products', $employee);
			$module->employee = $form->employee = $employee;
			$module->title = 'Sales Report for '.$employee->name;
		} elseif (isset($location->guid)) {
			$module->title = 'Sales Report for '.$location->name;
		} else {
			$location = $_SESSION['user']->group;
			$module->all = true;
			$module->title = 'Sales Report for All Locations';
		}
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $form->location = $location->guid;
		$form->employees = $pines->com_hrm->get_employees();
		$module->descendents = $form->descendents = $descendents;
		$selector['tag'] = array('com_sales', 'sale');
		$selector['data'] = array('status', 'paid');
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$selector['data'] = array('status', 'processed');
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->invoices = array_merge($sales, $returns);
	}

	/**
	 * Creates and attaches a module which reports warehouse items.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The location to report sales for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The product details report module.
	 */
	function report_warehouse($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_warehouse', 'content');
		
		$selector = array('&', 'tag' => array('com_sales', 'sale'), 'strict' => array('status', 'paid'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->transactions = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
	}
}

?>