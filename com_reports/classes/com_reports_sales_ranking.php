<?php

/**
 * com_reports_sales_ranking class.
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
 * A list of monthly sales rankings.
 *
 * @package Pines
 * @subpackage com_reports
 */
class com_reports_sales_ranking extends entity {
	/**
	 * Load a sales ranking.
	 * @param int $id The ID of the ranking to load, 0 for a new ranking.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_reports', 'sales_ranking');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		$this->start_date = strtotime(date('m/01/Y 00:00:00'));
		$this->end_date = strtotime('+1 month 00:00:00', $this->start_date);
		$this->top_location = $_SESSION['user']->group;
		$this->calc_nh_goals = true;
		$this->only_below = true;
		$this->sales_goals = array();
	}

	/**
	 * Create a new instance.
	 * @return com_reports_sales_ranking The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the sales ranking.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted sales ranking [$this->name].", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the sales ranking.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_reports', 'form_sales_ranking', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Creates and attaches a module which reports sales rankings.
	 * 
	 * @return module The sales ranking report module.
	 */
	function rank() {
		global $pines;

		$module = new module('com_reports', 'view_sales_rankings', 'content');
		$module->entity = $this;
		if ($this->final)
			return $module;

		// Get employees and locations.
		$group = $this->top_location;
		$locations = (array) $group->get_descendents();
		$users = (array) $group->get_users(true);
		$employees = array();
		foreach ($users as $cur_user) {
			// Skip users who only have secondary groups.
			if (!isset($cur_user->group->guid) || !($cur_user->group->is($group) || $cur_user->group->in_array($locations)))
				continue;
			// Skip users who aren't employees.
			if (!$cur_user->employee)
				continue;
			$employees[] = com_hrm_employee::factory($cur_user->guid);
		}
		unset($users);
		if (!$this->only_below)
			$locations[] = $group;

		// Date setup for different weekly and monthly breakdowns.
		$secperday = 60 * 60 * 24;
		if ($this->end_date > time()) {
			$days_passed = round((time() - $this->start_date) / $secperday);
			$days_total = round(($this->end_date - $this->start_date) / $secperday);
			if (format_date(time(), 'custom', 'w') == '1')
				$current_start = strtotime('00:00:00', time());
			else
				$current_start = strtotime('00:00:00', strtotime('last Monday'));
			if (format_date(time(), 'custom', 'w') == '0')
				$current_end = strtotime('23:59:59', time()) + 1;
			else
				$current_end = strtotime('23:59:59', strtotime('next Sunday')) + 1;
		} else {
			$days_passed = $days_total = round(($this->end_date - $this->start_date) / $secperday);
			$current_start = strtotime('00:00:00', strtotime('last Monday', $this->end_date));
			$current_end = strtotime('23:59:59', $this->end_date) + 1;
		}
		$last_start = strtotime('-1 week', $current_start);
		$last_end = $current_start;

		// Build an array to hold total data.
		$ranking_employee = array();
		$module->mifi_checks = $pines->depend->check('component', 'com_mifi');
		foreach ($employees as $cur_employee) {
			// Get all apps for the employee.
			if ($module->mifi_checks) {
				$current_apps = $pines->entity_manager->get_entities(
						array('class' => com_mifi_application, 'skip_ac' => true),
						array('&',
							'tag' => array('com_mifi', 'application'),
							'gte' => array(
								array('p_cdate', $current_start),
								array('p_cdate', $this->start_date)
							),
							'lt' => array(
								array('p_cdate', $current_end),
								array('p_cdate', $this->end_date)
							),
							'ref' => array('user', $cur_employee),
						)
					);
				$last_apps = $pines->entity_manager->get_entities(
						array('class' => com_mifi_application, 'skip_ac' => true),
						array('&',
							'tag' => array('com_mifi', 'application'),
							'gte' => array(
								array('p_cdate', $last_start),
								array('p_cdate', $this->start_date)
							),
							'lt' => array(
								array('p_cdate', $last_end),
								array('p_cdate', $this->end_date)
							),
							'ref' => array('user', $cur_employee)
						)
					);
				$mtd_apps = $pines->entity_manager->get_entities(
						array('class' => com_mifi_application, 'skip_ac' => true),
						array('&',
							'tag' => array('com_mifi', 'application'),
							'gte' => array('p_cdate', $this->start_date),
							'lt' => array('p_cdate', $this->end_date),
							'ref' => array('user', $cur_employee)
						)
					);
			} else {
				$current_apps = $last_apps = $mtd_apps = array();
			}
			$ranking_employee[$cur_employee->guid] = array(
				'entity' => $cur_employee,
				'location' => $cur_employee->group,
				'district' => $cur_employee->group->parent,
				'current' => 0.00,
				'last' => 0.00,
				'mtd' => 0.00,
				'current_apps' => count($current_apps),
				'last_apps' => count($last_apps),
				'mtd_apps' => count($mtd_apps),
				'trend' => 0.00,
				'pct' => 0.00,
				'goal' => (isset($this->sales_goals[$cur_employee->guid]) ? $this->sales_goals[$cur_employee->guid] : 0.00)
			);
			unset($current_apps, $last_apps, $mtd_apps);
		}
		$ranking_location = array();
		foreach ($locations as $cur_location) {
			// Get all apps for the location.
			if ($module->mifi_checks) {
				$groups = $cur_location->get_descendents(true);
				$current_apps = $pines->entity_manager->get_entities(
						array('class' => com_mifi_application, 'skip_ac' => true),
						array('&',
							'tag' => array('com_mifi', 'application'),
							'gte' => array(
								array('p_cdate', $current_start),
								array('p_cdate', $this->start_date)
							),
							'lt' => array(
								array('p_cdate', $current_end),
								array('p_cdate', $this->end_date)
							)
						),
						array('|',
							'ref' => array('group', $groups)
						)
					);
				$last_apps = $pines->entity_manager->get_entities(
						array('class' => com_mifi_application, 'skip_ac' => true),
						array('&',
							'tag' => array('com_mifi', 'application'),
							'gte' => array(
								array('p_cdate', $last_start),
								array('p_cdate', $this->start_date)
							),
							'lt' => array(
								array('p_cdate', $last_end),
								array('p_cdate', $this->end_date)
							)
						),
						array('|',
							'ref' => array('group', $groups)
						)
					);
				$mtd_apps = $pines->entity_manager->get_entities(
						array('class' => com_mifi_application, 'skip_ac' => true),
						array('&',
							'tag' => array('com_mifi', 'application'),
							'gte' => array('p_cdate', $this->start_date),
							'lt' => array('p_cdate', $this->end_date)
						),
						array('|',
							'ref' => array('group', $groups)
						)
					);
			} else {
				$current_apps = $last_apps = $mtd_apps = array();
			}
			$ranking_location[$cur_location->guid] = array(
				'entity' => $cur_location,
				'location' => $cur_location->parent,
				'current' => 0.00,
				'last' => 0.00,
				'mtd' => 0.00,
				'current_apps' => count($current_apps),
				'last_apps' => count($last_apps),
				'mtd_apps' => count($mtd_apps),
				'trend' => 0.00,
				'pct' => 0.00,
				'goal' => (isset($this->sales_goals[$cur_location->guid]) ? $this->sales_goals[$cur_location->guid] : 0.00),
				'child_count' => 0,
				'child_total' => 0.00
			);
			unset($current_apps, $last_apps, $mtd_apps);
		}

		// Recalculate new hire goals.
		if ($this->calc_nh_goals) {
			$nh_time = time();
			if ($current_end < $nh_time)
				$nh_time = $current_end;
			foreach ($ranking_employee as &$cur_rank) {
				if (!$cur_rank['entity']->new_hire)
					continue;
				if (!isset($cur_rank['entity']->training_completion_date)) {
					// Set goal to false if they have no training completion date.
					$cur_rank['goal'] = false;
					continue;
				}
				$weeks_worked = ceil(($nh_time - $cur_rank['entity']->training_completion_date) / (60 * 60 * 24 * 7));
				if ($weeks_worked < 0) {
					// Also set it to false if training completion date is in the future.
					$cur_rank['goal'] = false;
				} elseif ($weeks_worked >= 5) {
					$cur_rank['goal'] = 20000;
				} else {
					$goal_array = array(
						1 => 3000,
						2 => 7000,
						3 => 12000,
						4 => 17000
					);
					$cur_rank['goal'] = $goal_array[$weeks_worked];
				}
			}
			unset($cur_rank);
		}

		// Get all the sales and returns in the given time period.
		$sales = $pines->entity_manager->get_entities(
				array('class' => com_sales_sale, 'skip_ac' => true),
				array('&',
					'tag' => array('com_sales', 'sale'),
					'strict' => array('status', 'paid'),
					'gte' => array('tender_date', $this->start_date),
					'lt' => array('tender_date', $this->end_date)
				)
			);
		$returns = $pines->entity_manager->get_entities(
				array('class' => com_sales_return, 'skip_ac' => true),
				array('&',
					'tag' => array('com_sales', 'return'),
					'strict' => array('status', 'processed'),
					'gte' => array('process_date', $this->start_date),
					'lt' => array('process_date', $this->end_date)
				)
			);

		// Total all the sales and returns by employee and location.
		foreach ($sales as $cur_sale) {
			foreach ($cur_sale->products as $cur_product) {
				if (!isset($cur_product['salesperson']))
					continue;
				if (isset($ranking_employee[$cur_product['salesperson']->guid])) {
					$ranking_employee[$cur_product['salesperson']->guid]['mtd'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
					if ($cur_sale->tender_date >= $current_start && $cur_sale->tender_date <= $current_end)
						$ranking_employee[$cur_product['salesperson']->guid]['current'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
					elseif ($cur_sale->tender_date >= $last_start && $cur_sale->tender_date <= $last_end)
						$ranking_employee[$cur_product['salesperson']->guid]['last'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
				}
				$parent = $cur_sale->group;
				while (isset($parent->guid)) {
					if (isset($ranking_location[$parent->guid])) {
						$ranking_location[$parent->guid]['mtd'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
						if ($cur_sale->tender_date >= $current_start && $cur_sale->tender_date <= $current_end)
							$ranking_location[$parent->guid]['current'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
						elseif ($cur_sale->tender_date >= $last_start && $cur_sale->tender_date <= $last_end)
							$ranking_location[$parent->guid]['last'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
					}
					$parent = $parent->parent;
				}
			}
		}
		foreach ($returns as $cur_return) {
			foreach ($cur_return->products as $cur_product) {
				if (!isset($cur_product['salesperson']))
					continue;
				if (isset($ranking_employee[$cur_product['salesperson']->guid])) {
					$ranking_employee[$cur_product['salesperson']->guid]['mtd'] -= ($cur_product['line_total'] - (float) $cur_product['specials_total']);
					if ($cur_return->process_date >= $current_start && $cur_return->process_date <= $current_end)
						$ranking_employee[$cur_product['salesperson']->guid]['current'] -= ($cur_product['line_total'] - (float) $cur_product['specials_total']);
					elseif ($cur_return->process_date >= $last_start && $cur_return->process_date <= $last_end)
						$ranking_employee[$cur_product['salesperson']->guid]['last'] -= ($cur_product['line_total'] - (float) $cur_product['specials_total']);
				}
				$parent = $cur_return->group;
				while (isset($parent->guid)) {
					if (isset($ranking_location[$parent->guid])) {
						$ranking_location[$parent->guid]['mtd'] -= ($cur_product['line_total'] - (float) $cur_product['specials_total']);
						if ($cur_return->process_date >= $current_start && $cur_return->process_date <= $current_end)
							$ranking_location[$parent->guid]['current'] -= ($cur_product['line_total'] - (float) $cur_product['specials_total']);
						elseif ($cur_return->process_date >= $last_start && $cur_return->process_date <= $last_end)
							$ranking_location[$parent->guid]['last'] -= ($cur_product['line_total'] - (float) $cur_product['specials_total']);
					}
					$parent = $parent->parent;
				}
			}
		}

		// Calculate trend and percent goal.
		foreach ($ranking_employee as &$cur_rank) {
			if ($days_passed > 0)
				$cur_rank['trend'] = ($cur_rank['mtd'] / $days_passed) * $days_total;
			else
				$cur_rank['trend'] = 0;

			if ($cur_rank['goal'] > 0)
				$cur_rank['pct'] = $cur_rank['trend'] / $cur_rank['goal'] * 100;
			else
				$cur_rank['pct'] = 0;
		}
		unset($cur_rank);
		foreach ($ranking_location as &$cur_rank) {
			if ($days_passed > 0)
				$cur_rank['trend'] = ($cur_rank['mtd'] / $days_passed) * $days_total;
			else
				$cur_rank['trend'] = 0;

			if ($cur_rank['goal'] > 0)
				$cur_rank['pct'] = $cur_rank['trend'] / $cur_rank['goal'] * 100;
			else
				$cur_rank['pct'] = 0;
			// Keep a total and average for parent locations.
			if (isset($ranking_location[$cur_rank['entity']->parent->guid])) {
				$ranking_location[$cur_rank['entity']->parent->guid]['child_count']++;
			}
		}
		unset($cur_rank);

		// Separate employees by new hires, and locations into tiers.
		// Determine district and location managers.
		$this->new_hires = array();
		$this->employees = array();
		foreach ($ranking_employee as $cur_rank) {
			if ($cur_rank['entity']->new_hire)
				$this->new_hires[] = $cur_rank;
			else
				$this->employees[] = $cur_rank;
			if (preg_match('/(manager|^dmt?$)/i', $cur_rank['entity']->job_title) && isset($ranking_location[$cur_rank['entity']->group->guid]))
				$ranking_location[$cur_rank['entity']->group->guid]['manager'] = $cur_rank['entity'];
		}
		$this->locations = array();
		foreach ($ranking_location as $cur_rank) {
			$parent_count = 0;
			$parent = $cur_rank['entity']->parent;
			while (isset($parent->guid) && $parent->in_array($locations)) {
				$parent_count++;
				$parent = $parent->parent;
			}
			if (!$this->locations[$parent_count])
				$this->locations[$parent_count] = array();
			$this->locations[$parent_count][] = $cur_rank;
		}
		ksort($this->locations);

		// Sort and rank by trend.
		usort($this->new_hires, array($this, 'sort_mtd'));
		$this->new_hires = array_values($this->new_hires);
		$rank = 1;
		foreach ($this->new_hires as &$cur_rank) {
			if ($cur_rank['goal'] !== false && $cur_rank['goal'] <= 0)
				continue;
			$cur_rank['rank'] = $rank;
			$rank++;
		}
		unset($cur_rank);
		usort($this->employees, array($this, 'sort_mtd'));
		$this->employees = array_values($this->employees);
		$rank = 1;
		foreach ($this->employees as &$cur_rank) {
			if ($cur_rank['goal'] !== false && $cur_rank['goal'] <= 0)
				continue;
			$cur_rank['rank'] = $rank;
			$rank++;
		}
		unset($cur_rank);
		$count = count($this->locations);
		foreach ($this->locations as $key => &$cur_location) {
			if ($key == $count - 1)
				usort($cur_location, array($this, 'sort_mtd'));
			else
				usort($cur_location, array($this, 'sort_avg'));
			$rank = 1;
			foreach ($cur_location as &$cur_rank) {
				if ($cur_rank['goal'] !== false && $cur_rank['goal'] <= 0)
					continue;
				$cur_rank['rank'] = $rank;
				$rank++;
			}
			unset($cur_rank);
		}

		return $module;
	}

	/**
	 * Sort by the Avg value.
	 *
	 * @param array $a The first entry.
	 * @param array $b The second entry.
	 * @return int The sort order.
	 * @access private
	 */
	private function sort_avg($a, $b) {
		$a_avg = round($a['child_count'] > 0 ? $a['trend'] / $a['child_count'] : 0, 2);
		$b_avg = round($b['child_count'] > 0 ? $b['trend'] / $b['child_count'] : 0, 2);
		if ($a_avg > $b_avg)
			return -1;
		if ($a_avg < $b_avg)
			return 1;
		return 0;
	}

	/**
	 * Sort by the MTD value.
	 *
	 * @param array $a The first entry.
	 * @param array $b The second entry.
	 * @return int The sort order.
	 * @access private
	 */
	private function sort_mtd($a, $b) {
		if ($a['mtd'] > $b['mtd'])
			return -1;
		if ($a['mtd'] < $b['mtd'])
			return 1;
		return 0;
	}
}

?>