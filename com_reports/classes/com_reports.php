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
/* @var $pines pines */
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
	 * @return module The module.
	 */
	function list_paystubs() {
		global $pines;

		$module = new module('com_reports', 'list_paystubs', 'content');
		$module->paystubs = $pines->entity_manager->get_entities(array('class' => com_reports_paystub), array('&', 'tag' => array('com_reports', 'paystub')));

		if ( empty($module->paystubs) )
			pines_notice('There are no completed paystubs to view.');

		return $module;
	}

	/**
	 * Creates and attaches a module which lists sales rankings.
	 *
	 * @return module The module.
	 */
	function list_sales_rankings() {
		global $pines;

		$module = new module('com_reports', 'list_sales_rankings', 'content');
		$module->rankings = $pines->entity_manager->get_entities(array('class' => com_reports_sales_ranking), array('&', 'tag' => array('com_reports', 'sales_ranking')));

		return $module;
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
	 * @return module The invoice summary module.
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
	 * Creates and attaches a module which reports employee daily attendance.
	 *
	 * @param int $date The date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The attendance report module.
	 */
	function daily_attendance($date, $location = null, $descendents = false) {
		global $pines;

		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;

		$module = new module('com_reports', 'attendance/daily_attendance', 'content');
		$module->date = $date;
		$module->location = $location;
		$module->descendents = $descendents;
		$module->attendance = array();
		// Only one day is in the date range, and it's in the current timezone.
		$start_date = strtotime('00:00:00', $date);
		$end_date = strtotime('23:59:59', $date) + 1;
		$employees = $pines->com_hrm->get_employees(true);
		foreach ($employees as $key => &$cur_employee) {
			if (!($cur_employee->in_group($location) || ($descendents && $cur_employee->is_descendent($location))))
				continue;
			$cur_array = array(
				'employee' => $cur_employee,
				'clocked_in' => null,
				'clocked_out' => null,
				'clocked_total' => 0,
				'clocked_ips' => array(),
				'scheduled_in' => null,
				'scheduled_out' => null,
				'scheduled_total' => 0,
			);
			// Did the employee clock in?
			foreach($cur_employee->timeclock->timeclock as $key => $entry) {
				// Ignore if it's not even in our time range.
				if ($entry['out'] < $start_date || $entry['in'] >= $end_date)
					continue;
				// Ignore any part of the clockin outside our time range.
				$in = $entry['in'];
				if ($in < $start_date)
					$in = $start_date;
				$out = $entry['out'];
				if ($out > $end_date)
					$out = $end_date;
				$time = $out - $in;
				if (!isset($cur_array['clocked_in']) || $cur_array['clocked_in'] > $in)
					$cur_array['clocked_in'] = $in;
				if (!isset($cur_array['clocked_out']) || $cur_array['clocked_out'] < $out)
					$cur_array['clocked_out'] = $out;
				$cur_array['clocked_total'] += $time;
				// IPs aren't always on all clockins.
				if (isset($entry['extras']['ip_in']) && !in_array($entry['extras']['ip_in'], $cur_array['clocked_ips']))
					$cur_array['clocked_ips'][] = $entry['extras']['ip_in'];
				if (isset($entry['extras']['ip_out']) && !in_array($entry['extras']['ip_out'], $cur_array['clocked_ips']))
					$cur_array['clocked_ips'][] = $entry['extras']['ip_out'];
			}
			// Get their scheduled time.
			$schedule = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					array('&',
						'tag' => array('com_calendar', 'event'),
						'lt' => array('start', $end_date),
						'gte' => array('end', $start_date),
						'ref' => array('employee', $cur_employee)
					)
				);
			foreach($schedule as $cur_schedule) {
				// Ignore any part of the clockin outside our time range.
				$in = $cur_schedule->start;
				if ($in < $start_date)
					$in = $start_date;
				$out = $cur_schedule->end;
				if ($out > $end_date)
					$out = $end_date;
				$time = $out - $in;

				// Compare scheduled time with our calculated time.
				$diff = ($cur_schedule->end - $cur_schedule->start) - (int) $cur_schedule->scheduled;
				// And subtract the difference from ours.
				$time -= $diff;

				if (!isset($cur_array['scheduled_in']) || $cur_array['scheduled_in'] > $in)
					$cur_array['scheduled_in'] = $in;
				if (!isset($cur_array['scheduled_out']) || $cur_array['scheduled_out'] < $out)
					$cur_array['scheduled_out'] = $out;
				$cur_array['scheduled_total'] += $time;
			}
			// Only add them if they were clocked or scheduled.
			if ($cur_array['clocked_total'] || $cur_array['scheduled_total'])
				$module->attendance[] = $cur_array;
		}
		unset($cur_employee);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports hours clocked.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param int $employee The employee to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The attendance report module.
	 */
	function hours_clocked($start_date = null, $end_date = null, $location = null, $employee = null, $descendents = false) {
		global $pines;

		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;

		$module = new module('com_reports', 'attendance/hours_clocked', 'content');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->location = $location;
		$module->descendents = $descendents;
		if (!isset($employee)) {
			$employees = $pines->com_hrm->get_employees(true);
			$module->employees = array();
			$totals = array();
			$total_group['scheduled'] = $total_group['clocked'] = $time_punch = $total_count = 0;
			foreach ($employees as $key => &$cur_employee) {
				if (!($cur_employee->in_group($location) || ($descendents && $cur_employee->is_descendent($location))))
					continue;
				$totals[$total_count]['scheduled'] = $totals[$total_count]['clocked'] = 0;
				$schedule = $pines->entity_manager->get_entities(
						array('class' => com_calendar_event),
						array('&',
							'tag' => array('com_calendar', 'event'),
							'gte' => array('start', $start_date),
							'lt' => array('end', $end_date),
							'ref' => array('employee', $cur_employee)
						)
					);
				foreach ($schedule as $cur_schedule)
					$totals[$total_count]['scheduled'] += $cur_schedule->scheduled;
				$totals[$total_count]['clocked'] = $cur_employee->timeclock->sum($start_date, $end_date);

				$module->employees[] = array(
					'employee' => $cur_employee,
					'scheduled' => round($totals[$total_count]['scheduled'] / 3600, 2),
					'clocked' => round($totals[$total_count]['clocked'] / 3600, 2),
					'variance' => round(($totals[$total_count]['clocked'] - $totals[$total_count]['scheduled']) / 3600, 2),
				);
				$total_group['scheduled'] += $totals[$total_count]['scheduled'];
				$total_group['clocked'] += $totals[$total_count]['clocked'];
				$total_count++;
			}
			unset($cur_employee);
			$module->totals = array(
				'scheduled' => round($total_group['scheduled'] / 3600, 2),
				'clocked' => round($total_group['clocked'] / 3600, 2),
				'variance' => round(($total_group['clocked'] - $total_group['scheduled']) / 3600, 2),
			);
		} else {
			$module->clocks = $module->dates = array();
			$clock_count = $date_count = 0;
			foreach($employee->timeclock->timeclock as $key => $entry) {
				if ( $module->all_time || ($entry['in'] >= $start_date && ($entry['out'] <= $end_date || !isset($entry['out']))) ) {
					if ($module->dates[$date_count]['date'] != format_date($entry['in'], 'date_sort')) {
						$date_count++;
						$module->dates[$date_count]['start'] = strtotime('00:00:00', $entry['in']);
						$module->dates[$date_count]['end'] = strtotime('23:59:59', $entry['out']) + 1;
						$module->dates[$date_count]['date'] = format_date($entry['in'], 'date_sort');
						$module->dates[$date_count]['scheduled'] = 0;
						$module->dates[$date_count]['total'] = 0;
					}
					$clock_count++;
					$module->clocks[$clock_count] = $entry;
					$module->clocks[$clock_count]['date'] = $date_count;
					$module->dates[$date_count]['total'] += $module->clocks[$clock_count]['total'] = $employee->timeclock->sum($entry['in'], isset($entry['out']) ? $entry['out'] : time());
				}
			}
			foreach ($module->dates as &$cur_date) {
				$scheduled = $pines->entity_manager->get_entities(
						array('class' => com_calendar_event),
						array('&',
							'tag' => array('com_calendar', 'event'),
							'gte' => array('start', $cur_date['start']),
							'lt' => array('end', $cur_date['end']),
							'ref' => array('employee', $employee)
						)
					);
				foreach ($scheduled as $cur_schedule) {
					if (!isset($cur_date['sched_start']) || $cur_date['sched_start'] > $cur_schedule->start)
						$cur_date['sched_start'] = $cur_schedule->start;
					if (!isset($cur_date['sched_end']) || $cur_date['sched_end'] < $cur_schedule->end)
						$cur_date['sched_end'] = $cur_schedule->end;
					$cur_date['scheduled'] += $cur_schedule->scheduled;
				}
				$cur_date['total_hours'] = floor($cur_date['total'] / 3600);
				$cur_date['total_mins'] = round(($cur_date['total'] / 60) - ($cur_date['total_hours'] * 60));
				$cur_date['variance'] = round(($cur_date['total'] - $cur_date['scheduled']) / 3600, 2);
			}
			unset($cur_date);
			$module->employee = $employee;
		}

		return $module;
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
	 * @param entity $employee The entity of the employee.
	 * @param float $payperhour This is passed in from report_payroll_summary.
	 * @param float $totalhours This is the total amount of hours work.
	 * @param float $totalpay This is the total they're being paid for the time period.
	 * @param float $salary This is the pay for the time period of salaried employees.
	 * @param float $commission This is the total commission.
	 * @param bool $hourreport This is a boolean variable that says weather the report will generate an individual hourly report or as commission vs draw statuses included.
	 * @param string $adjust This is the value of adjust total which is passed in sometimes.
	 * @param float $reghourpay The amount of pay for regular hours.
	 * @param float $overtimehourpay The amount of pay for overtime hours.
	 * @return module The payroll summary module.
	 */
	function report_payroll_individual($start_date = null, $end_date = null,  $employee = null, $payperhour = null, $totalhours =null, $totalpay = null, $salary = null, $commission = null, $hourreport = false, $adjust = null, $reghourpay = null, $overtimehourpay = null) {
		global $pines;
		$module = new module('com_reports', 'report_individual_payroll', 'content');
		$module->employee = $employee;
		$module->pay_per_hour = $payperhour;
		$module->total_hours = $totalhours;
		$module->total_pay = $totalpay;
		$module->salary = $salary;
		$module->commission = $commission;
		$module->adjust = 0;
		if ($hourreport == 'true')
			$module->hourreport = true;
		else
			$module->hourreport = false;
		// Number of hours in the weeks worked in the date range. (All full weeks.)
		$time_diff_weeks_hours = (($end_date - $start_date)*(1/604800))*40;
		if ( (float) $totalhours > (float) $time_diff_weeks_hours ) {
			$module->reg_hours = $time_diff_weeks_hours;
			$module->overtime = $totalhours - $time_diff_weeks_hours;
		} else {
			$module->reg_hours = $totalhours;
			$module->overtime = 0;
		}
		// For the reports coming from the report summary not the hour summary table
		// determine the amount of hours from the pay already calculated
		if (!$module->hourreport) {
			$module->reg_hours = $reghourpay / $module->employee->pay_rate;
			$module->overtime = $overtimehourpay / ($module->employee->pay_rate * 1.5);
		}
		// Get bonuses.
		$module->bonuses = $pines->entity_manager->get_entities(
				array('class' => com_hrm_bonus),
				array('&',
					'tag' => array('com_hrm', 'bonus'),
					'gte' => array('date', (int) $start_date),
					'lt' => array('date', (int) $end_date),
					'ref' => array('employee', $module->employee)
				)
			);
		$module->bonus_total = 0;
		foreach ($module->bonuses as $cur_bonus) {
			$module->bonus_total += $cur_bonus->amount;
		}

		// If this is a monthly report then we need to only include adjustments
		// for most current week since the weekly report will have already
		// included the adjusments for the previous weeks.
		if ($module->hourreport)
			$time = $start_date;
		else
			$time = $end_date - 604800;

		// Get adjustments.
		$module->adjustments = $pines->entity_manager->get_entities(
				array('class' => com_hrm_adjustment),
				array('&',
					'tag' => array('com_hrm', 'adjustment'),
					'gte' => array('date', (int) $time),
					'lt' => array('date', (int) $end_date),
					'ref' => array('employee', $module->employee)
				)
			);
		$module->adjustment_total = 0;
		foreach ($module->adjustments as $cur_adjustment) {
			$module->adjustment_total += $cur_adjustment->amount;
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
		$selector['ref'] = array('user', $module->employee);
		$selector['tag'] = array('com_sales', 'sale');
		$module->sales = $pines->entity_manager->get_entities(
				array('class' => com_sales_sale),
				$selector
			);
		// Make sure this sale is not attached to any returns.
		foreach ($module->sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
					array('class' => com_sales_return, 'skip_ac' => true),
					array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $cur_sale))
				);
			if (isset($return->guid))
				unset($module->sales[$key]);
		}
		// If this is a summary report we need the amount adjustment amount 
		// of what we've already paid them.
		if (!$module->hourreport) {

			if($module->employee->pay_type == 'salary') {
				$module->adjust = (-1 * (1814400 / 31536000)) * $employee->pay_rate;
			} else {
				$week = ($module->employee->timeclock->sum($start_date, ($start_date + 604800))/3600);
				if ($week > 40)
					$module->adjust += (40 * $module->employee->pay_rate) + ($module->employee->pay_rate * 1.5 * ($week -40));
				else
					$module->adjust += $week * $module->employee->pay_rate;
				$week = ($module->employee->timeclock->sum(($start_date +604800), ($start_date + 1209600))/3600);
				if ($week > 40)
					$module->adjust += (40 * $module->employee->pay_rate) + ($module->employee->pay_rate * 1.5 * ($week -40));
				else
					$module->adjust += $week * $module->employee->pay_rate;
				$week = ($module->employee->timeclock->sum(($start_date +1209600), ($start_date + 1814400))/3600);
				if ($week > 40)
					$module->adjust += (40 * $module->employee->pay_rate) + ($module->employee->pay_rate * 1.5 * ($week -40));
				else
					$module->adjust += $week * $module->employee->pay_rate;

				$module->adjust = $module->adjust * -1;
			}
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
	 * Keeps all employees as hourly or salary. Sums up their commissions, but
	 * does not add it into their pay.
	 * 
	 * Hourly employees' pay is totaled for the hourly pay and then their
	 * commission pay. If their commission is greater than their hourly they get
	 * a status of commission. If their hourly is more they get a draw status.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The invoice summary module.
	 */
	function report_payroll_hourly($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_payroll_hourly', 'content');

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

		$module->sales = $pines->entity_manager->get_entities(
				array('class' => com_sales_sale),
				$selector,
				$or
			);
		// Make sure sales are not attached to any returns.
		foreach ($module->sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
					array('class' => com_sales_return, 'skip_ac' => true),
					array('&',
						'tag' => array('com_sales', 'return'),
						'ref' => array('sale', $cur_sale)
					)
				);
			if (isset($return->guid))
				unset($module->sales[$key]);
		}
		unset($cur_sale);

		// Find employees in the location or its descendents.
		$employees = $pines->com_hrm->get_employees(true);
		$module->employees = array();
		foreach ($employees as &$cur_employee) {
			if (!($cur_employee->in_group($location) || ($descendents && $cur_employee->is_descendent($location))))
				continue;
			$module->employees[] = array('entity' => $cur_employee);
		}
		unset($cur_empoloyee);

		// Calculate time totals based on scheduled vs clocked hours.
		$totals = array();
		$total_group['scheduled'] = $total_group['clocked'] = $time_punch = $total_count = 0;
		foreach ($module->employees as &$cur_employee) {
			$cur_employee['bonus'] = 0;
			$totals[$total_count]['scheduled'] = $totals[$total_count]['clocked'] = 0;
			$schedule = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					array('&',
						'tag' => array('com_calendar', 'event'),
						'gte' => array('start', $start_date),
						'lt' => array('end', $end_date),
						'ref' => array('employee', $cur_employee['entity'])
					)
				);

			// Get adjustments for employee as well.
			$adjustments = (array) $pines->entity_manager->get_entities(
					array('class' => com_hrm_bonus),
					array('&',
						'tag' => array('com_hrm', 'adjustment'),
						'gte' => array('date', (int)$start_date),
						'lt' => array('date', (int)$end_date),
						'ref' => array('employee', $cur_employee['entity'])
					)
				);
			// Add the adjustments.
			foreach($adjustments as &$cur_adjustments){
					$cur_employee['adjustments'] += $cur_adjustments->amount;
			}
			unset($cur_adjustments);
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
			$saletotals[$guid] += $cur_sale->subtotal;
			$numbersales[$guid]++;
			$total_num_sales++;
			// Add the set commission, or add 6% if no commission was calculated.
			foreach ($cur_sale->products as $cur_product) {
				if (isset($cur_product['commission']))
					$commission_array[$guid] += round($cur_product['commission'], 2);
				else
					$commission_array[$guid] += round($cur_product['line_total'] * 0.06, 2);
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
		$module->group_adjustments = 0;
		$module->group_bonus = 0;
		$module->group_pay_total_with_reimburse = 0;

		foreach ($module->employees as &$cur_employee) {
			// Determines what the employees commision is for this time period
			// otherwise it get the default 6% commission.
			if (array_key_exists($cur_employee['entity']->guid, $commission_array))
				$cur_employee['commission'] = $commission_array[$cur_employee['entity']->guid];
			else
				$cur_employee['commission'] = 0.06 * $cur_employee['sales_total'];
			// Get the amount of hours worked.
			$cur_employee['hour_total'] = $cur_employee['clocked'];
			// Figure out if they have any overtime.
			if ($cur_employee['hour_total'] > 40) {
				$cur_employee['reghours'] = $cur_employee['entity']->pay_rate * 40;
				$cur_employee['overtimehours'] = ($cur_employee['hour_total'] - 40) * ($cur_employee['entity']->pay_rate * 1.5);
			} else {
				$cur_employee['reghours'] = ($cur_employee['entity']->pay_rate * $cur_employee['hour_total']);
				$cur_employee['overtimehours'] = 0;
			}
			// Add together their total pay for this time period and determine
			// if they're draw or commission.
			$cur_employee['hour_pay_total'] = $cur_employee['reghours'] + $cur_employee['overtimehours'];

			// All employees are treated as hourly on this report except salary.
			$cur_employee['pay_total'] = $cur_employee['hour_pay_total'] = $cur_employee['reghours'] + $cur_employee['overtimehours'];
			$cur_employee['commission_status'] = 'hourly';

			// Ensures no 0 amount for the total_rate.
			if ($cur_employee['clocked'] == 0)
				$cur_employee['total_rate'] = $cur_employee['pay_total'];
			else
				$cur_employee['total_rate'] = $cur_employee['pay_total'] / $cur_employee['clocked'];
			// Does computations for salaried employee variables.
			if ($cur_employee['entity']->pay_type == 'salary') {
				$cur_employee['commission_status'] = 'salary';
				if (isset($start_date)) {
					// Figure out the amount of days in this pay period
					$days = (int)(($end_date - $start_date) / 86400);
					// Multiply by the amount of pay per day.
					$cur_employee['salary_pay_period'] = $days * ($cur_employee['entity']->pay_rate / 365);
					$module->group_salary_total += round($cur_employee['salary_pay_period'], 2);
					$cur_employee['pay_total'] = $cur_employee['salary_pay_period'];
				} else {
					$cur_employee['salary_pay_period'] = $cur_employee['entity']->pay_rate;
					$module->group_salary_total += round($cur_employee['salary_pay_period'], 2);
					$cur_employee['pay_total'] = $cur_employee['salary_pay_period'];
				}
			}
			// Add in adjustments.
			$cur_employee['total_with_reimburse'] = $cur_employee['pay_total'] + $cur_employee['adjustments'];
			// Add the total amount of hourly employees.
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
			$module->group_adjustments += $cur_employee['adjustments'];
			$module->group_bonus += $cur_employee['bonus'];
			$module->group_pay_total_with_reimburse += $cur_employee['total_with_reimburse'];
		}
		unset($cur_empoloyee);

		if ($pay_rate['people'] != 0) {
			$module->group_percent_rate = $group_percent_rate / $pay_rate['people'];
			$module->pay_rate_total = $pay_rate['rate'] / $pay_rate['people'];
		} else {
			$module->group_percent_rate = 0;
			$module->pay_rate_total = 0;
		}
		// Get total sales.
		$module->group_sales = 0;
		foreach ($saletotals as $value) {
			$module->group_sales += $value;
		}

		return $module;
	}

	/**
	 * Creates and attaches a module which summarizes employee payroll.
	 * 
	 * Hourly employees pay is totaled for the hourly pay and then their
	 * commission pay. If their commission is greater than their hourly they get
	 * a status of commission. If their hourly is more they get a draw status.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The payroll summary module.
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
			$cur_employee['bonus'] = 0;
			$totals[$total_count]['scheduled'] = $totals[$total_count]['clocked'] = 0;
			$schedule = $pines->entity_manager->get_entities(
					array('class' => com_calendar_event),
					array('&',
						'tag' => array('com_calendar', 'event'),
						'gte' => array('start', (int) $start_date),
						'lt' => array('end', (int) $end_date),
						'ref' => array('employee', $cur_employee['entity'])
					)
				);

			// Get bonuses for employee too.
			$bonuses = (array) $pines->entity_manager->get_entities(
					array('class' => com_hrm_bonus),
					array('&',
						'tag' => array('com_hrm', 'bonus'),
						'gte' => array('date', (int) $start_date),
						'lt' => array('date', (int) $end_date),
						'ref' => array('employee', $cur_employee['entity'])
					)
				);
			// Add the bonuses.
			foreach ($bonuses as $cur_bonus) {
					$cur_employee['bonus'] += $cur_bonus->amount;
			}
			// Get adjustments for employee as well.
			// Only add adjustments for this week since the other ones would
			// have been added on the weekly hourly.
			$adjustments = (array) $pines->entity_manager->get_entities(
					array('class' => com_hrm_bonus),
					array('&',
						'tag' => array('com_hrm', 'adjustment'),
						'gte' => array('date', (int) ($end_date - 604800)),
						'lt' => array('date', (int) $end_date),
						'ref' => array('employee', $cur_employee['entity'])
					)
				);
			// Add the adjustments.
			foreach ($adjustments as $cur_adjustments) {
					$cur_employee['adjustments'] += $cur_adjustments->amount;
			}
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
		$module->group_bonus = 0;
		$module->group_adjustments = 0;
		$module->group_pay_total_with_reimburse = 0;

		foreach ($module->employees as &$cur_employee) {
			// Determine what the employee's commission is for this time period
			// otherwise it gets the default 6% commission.
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
			// Determine the amount of pay for each week of the time period this month
			$cur_employee['reghours'] = 0;
			$cur_employee['overtimehours'] = 0;

			// Calculate the employees total hours in the first week.
			$week = $cur_employee['entity']->timeclock->sum($start_date, strtotime('+1 week', $start_date))/3600;
			if ($week > 40) {
				$cur_employee['reghours'] += (40 * $cur_employee['entity']->pay_rate);
				$cur_employee['overtimehours'] += ($cur_employee['entity']->pay_rate * 1.5 * ($week-40));
			} else
				$cur_employee['reghours'] += $week * $cur_employee['entity']->pay_rate;
			// Calculate the employees total hours in the second week.
			$week = $cur_employee['entity']->timeclock->sum(strtotime('+1 week', $start_date), strtotime('+2 weeks', $start_date))/3600;
			if ($week > 40) {
				$cur_employee['reghours'] += (40 * $cur_employee['entity']->pay_rate);
				$cur_employee['overtimehours'] += ($cur_employee['entity']->pay_rate * 1.5 * ($week-40));
			} else
				$cur_employee['reghours'] += $week * $cur_employee['entity']->pay_rate;
			// Calculate the employees total hours in the third week.
			$week = $cur_employee['entity']->timeclock->sum(strtotime('+2 weeks', $start_date), strtotime('+3 weeks', $start_date))/3600;
			if ($week > 40) {
				$cur_employee['reghours'] += (40 * $cur_employee['entity']->pay_rate);
				$cur_employee['overtimehours'] += ($cur_employee['entity']->pay_rate * 1.5 * ($week-40));
			} else
				$cur_employee['reghours'] += $week * $cur_employee['entity']->pay_rate;
			// This will be the amount we take out because we've already paid them this much.
			$adjust = -1 * ($cur_employee['reghours']+$cur_employee['overtimehours']);
			// Calculate the employees total hours in the last week (or more).
			$week = $cur_employee['entity']->timeclock->sum(strtotime('+3 weeks', $start_date), $end_date)/3600;
			if ($week > 40) {
				$cur_employee['reghours'] += (40 * $cur_employee['entity']->pay_rate);
				$cur_employee['overtimehours'] += ($cur_employee['entity']->pay_rate * 1.5 * ($week-40));
			} else
				$cur_employee['reghours'] += $week * $cur_employee['entity']->pay_rate;

			// Add to the adjustments for hourly and commissions.
			if (!$cur_employee['adjustments'])
				$cur_employee['adjustments'] = 0;
			if ($cur_employee['entity']->pay_type != 'salary')
				$cur_employee['adjustments'] += $adjust;
			else {
				// Take away the first 3 weeks of the pay for salaried employees so their
				// total pay reflects this week's pay.
				// Determines the portion of salary that they've already been
				// paid that needs to be deducted.
				$adjust = (-1 * (1814400 / 31536000)) * $cur_employee['entity']->pay_rate;
				$cur_employee['adjustments'] += $adjust;
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
			else {
				if ($cur_employee['entity']->pay_type == 'hourly' || $cur_employee['commission_status'] == 'draw')
					$cur_employee['total_rate'] = $cur_employee['entity']->pay_rate;
				elseif ($cur_employee['commission_status'] == 'commission') {
					if ($cur_employee['clocked'] != 0)
						$cur_employee['total_rate'] = $cur_employee['commission'] / $cur_employee['clocked'];
					else
						$cur_employee['total_rate'] = $cur_employee['commission'];
				}

			}
			// Does computations for salaried employee variables.
			if ($cur_employee['entity']->pay_type == 'salary') {
				$cur_employee['commission_status'] = 'salary';
				if (isset($start_date)) {
					// Creates a ratio of the days in this schedule to be 
					// multiplied by their pay rate.
					$ratio = (($end_date - $start_date ) / 31536000) ;
					$cur_employee['salary_pay_period'] = $cur_employee['entity']->pay_rate * $ratio;
					$module->group_salary_total += round($cur_employee['salary_pay_period'], 2);
					$cur_employee['pay_total'] = $cur_employee['salary_pay_period'];
				} else {
					$cur_employee['salary_pay_period'] = $cur_employee['entity']->pay_rate;
					$module->group_salary_total += round($cur_employee['salary_pay_period'], 2);
					$cur_employee['pay_total'] = $cur_employee['salary_pay_period'];
				}
			}
			// Add bonuses and get a total for the pay with reimbursements.
			$cur_employee['pay_total'] += $cur_employee['bonus'] ;
			$cur_employee['total_with_reimburse'] = $cur_employee['pay_total'] + $cur_employee['adjustments'];

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
			$module->group_adjustments += $cur_employee['adjustments'];
			$module->group_bonus += $cur_employee['bonus'];
			$module->group_pay_total_with_reimburse += $cur_employee['total_with_reimburse'];
		}
		unset($cur_empoloyee);

		if($pay_rate['people']!= 0) {
			$module->group_percent_rate = $group_percent_rate / $pay_rate['people'];
			$module->pay_rate_total = $pay_rate['rate'] / $pay_rate['people'];
			$module->commission_percent = $commission_percent['commission'] / ($commission_percent['commission']+$commission_percent['draw']);
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

		return $module;
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

		return $module;
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

		return $module;
	}
}

?>