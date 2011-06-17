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
		// Defaults.
		$this->start_date = strtotime(date('m/01/Y 00:00:00'));
		$this->end_date = strtotime('+1 month 00:00:00', $this->start_date);
		$this->top_location = $_SESSION['user']->group;
		$this->only_below = true;
		$this->sales_goals = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data(), $entity->get_sdata());
		}
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
		global $pines;
		
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
		$module->rankings = array();

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
		if (format_date(time(), 'custom', 'w') == '1') {
			$current_start = strtotime('00:00:00', time());
		} else {
			$current_start = strtotime('00:00:00', strtotime('last Monday'));
		}
		if (format_date(time(), 'custom', 'w') == '0') {
			$current_end = strtotime('23:59:59', time()) + 1;
		} else {
			$current_end = strtotime('23:59:59', strtotime('next Sunday')) + 1;
		}
		if ($this->end_date > time()) {
			$days_passed = (int) format_date(time(), 'custom', 'j');
			$days_total = (int) format_date(time(), 'custom', 't');
		} else {
			$days_passed = (int) format_date($this->end_date, 'custom', 'j');
			$days_total = (int) format_date($this->end_date, 'custom', 't');
			$current_start = strtotime('00:00:00', strtotime('last Monday', $this->end_date));
			$current_end = strtotime('23:59:59', $this->end_date) + 1;
		}
		$last_start = strtotime('-1 week', $current_start);
		$last_end = strtotime('+1 week', $last_start);

		// Build an array to hold total data.
		$ranking_employee = array();
		foreach ($employees as $cur_employee) {
			$ranking_employee[$cur_employee->guid] = array(
				'entity' => $cur_employee,
				'current' => 0.00,
				'last' => 0.00,
				'mtd' => 0.00,
				'trend' => 0.00,
				'pct' => 0.00,
				'goal' => (isset($this->sales_goals[$cur_employee->guid]) ? $this->sales_goals[$cur_employee->guid] : 0.00)
			);
		}
		$ranking_location = array();
		foreach ($locations as $cur_location) {
			$ranking_location[$cur_location->guid] = array(
				'entity' => $cur_location,
				'current' => 0.00,
				'last' => 0.00,
				'mtd' => 0.00,
				'trend' => 0.00,
				'pct' => 0.00,
				'goal' => (isset($this->sales_goals[$cur_location->guid]) ? $this->sales_goals[$cur_location->guid] : 0.00),
				'child_count' => 0,
				'child_total' => 0.00
			);
		}

		// Get all the sales and returns in the given time period.
		$sales = $pines->entity_manager->get_entities(
				array('class' => com_sales_sale),
				array('&',
					'tag' => array('com_sales', 'sale'),
					'strict' => array('status', 'paid'),
					'gte' => array('tender_date', $this->start_date),
					'lt' => array('tender_date', $this->end_date)
				)
			);
		$returns = $pines->entity_manager->get_entities(
				array('class' => com_sales_return),
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
					$ranking_employee[$cur_product['salesperson']->guid]['mtd'] += $cur_product['line_total'];
					if ($cur_sale->tender_date >= $current_start && $cur_sale->tender_date <= $current_end)
						$ranking_employee[$cur_product['salesperson']->guid]['current'] += $cur_product['line_total'];
					elseif ($cur_sale->tender_date >= $last_start && $cur_sale->tender_date <= $last_end)
						$ranking_employee[$cur_product['salesperson']->guid]['last'] += $cur_product['line_total'];
				}
				$parent = $cur_product['salesperson']->group;
				while (isset($parent->guid)) {
					if (isset($ranking_location[$parent->guid])) {
						$ranking_location[$parent->guid]['mtd'] += $cur_product['line_total'];
						if ($cur_sale->tender_date >= $current_start && $cur_sale->tender_date <= $current_end)
							$ranking_location[$parent->guid]['current'] += $cur_product['line_total'];
						elseif ($cur_sale->tender_date >= $last_start && $cur_sale->tender_date <= $last_end)
							$ranking_location[$parent->guid]['last'] += $cur_product['line_total'];
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
					$ranking_employee[$cur_product['salesperson']->guid]['mtd'] -= $cur_product['line_total'];
					if ($cur_return->tender_date >= $current_start && $cur_return->tender_date <= $current_end)
						$ranking_employee[$cur_product['salesperson']->guid]['current'] -= $cur_product['line_total'];
					elseif ($cur_return->tender_date >= $last_start && $cur_return->tender_date <= $last_end)
						$ranking_employee[$cur_product['salesperson']->guid]['last'] -= $cur_product['line_total'];
				}
				$parent = $cur_product['salesperson']->group;
				while (isset($parent->guid)) {
					if (isset($ranking_location[$parent->guid])) {
						$ranking_location[$parent->guid]['mtd'] -= $cur_product['line_total'];
						if ($cur_return->tender_date >= $current_start && $cur_return->tender_date <= $current_end)
							$ranking_location[$parent->guid]['current'] -= $cur_product['line_total'];
						elseif ($cur_return->tender_date >= $last_start && $cur_return->tender_date <= $last_end)
							$ranking_location[$parent->guid]['last'] -= $cur_product['line_total'];
					}
					$parent = $parent->parent;
				}
			}
		}

//var_dump($days_passed, $days_total);
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
		$module->new_hires = array();
		$module->employees = array();
		foreach ($ranking_employee as $cur_rank) {
			if ($cur_rank['entity']->new_hire)
				$module->new_hires[] = $cur_rank;
			else
				$module->employees[] = $cur_rank;
			if (preg_match('/(manager|^dmt?$)/i', $cur_rank['entity']->job_title) && isset($ranking_location[$cur_rank['entity']->group->guid]))
				$ranking_location[$cur_rank['entity']->group->guid]['manager'] = $cur_rank['entity'];
		}
		$module->locations[] = array();
		foreach ($ranking_location as $cur_rank) {
			$parent_count = 0;
			$parent = $cur_rank['entity']->parent;
			while (isset($parent->guid) && $parent->in_array($locations)) {
				$parent_count++;
				$parent = $parent->parent;
			}
			if (!$module->locations[$parent_count])
				$module->locations[$parent_count] = array();
			$module->locations[$parent_count][] = $cur_rank;
		}
		ksort($module->locations);

		// Sort and rank by trend percentage.
		usort($module->new_hires, array($this, 'sort_ranks'));
		$rank = 1;
		foreach ($module->new_hires as &$cur_rank) {
			$cur_rank['rank'] = $rank;
			$rank++;
		}
		unset($cur_rank);
		usort($module->employees, array($this, 'sort_ranks'));
		$rank = 1;
		foreach ($module->employees as &$cur_rank) {
			$cur_rank['rank'] = $rank;
			$rank++;
		}
		unset($cur_rank);
		foreach ($module->locations as &$cur_location) {
			usort($cur_location, array($this, 'sort_ranks'));
			$rank = 1;
			foreach ($cur_location as &$cur_rank) {
				$cur_rank['rank'] = $rank;
				$rank++;
			}
			unset($cur_rank);
		}

		return $module;
	}

	/**
	 * Sort by the trend percentage.
	 *
	 * @param array $a The first entry.
	 * @param array $b The second entry.
	 * @return int The sort order.
	 * @access private
	 */
	private function sort_ranks($a, $b) {
		if ($a['pct'] > $b['pct'])
			return -1;
		if ($a['pct'] < $b['pct'])
			return 1;
		return 0;
	}
}

?>