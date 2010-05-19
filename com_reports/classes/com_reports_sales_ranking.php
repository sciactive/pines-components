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
		$this->goals = array();
		$this->start_date = strtotime('first day');
		$this->end_date = strtotime('last day');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
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
		pines_log("Deleted sales ranking [$this->guid].", 'notice');
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
		$module->employees = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));

		foreach ($module->employees as $key => $value) {
			if (!isset($value->user_account))
				unset($module->employees[$key]);
		}

		return $module;
	}

	/**
	 * Creates and attaches a module which reports sales rankings.
	 * 
	 * @param group $location The location to list the rankings for.
	 * @return module The sales ranking report module.
	 */
	function rank($location = null) {
		global $pines;

		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		
		$form = new module('com_reports', 'form_sales_rankings', 'left');
		$module = new module('com_reports', 'rank_sales', 'content');
		$module->entity = $form->entity = $this;
		$module->location = $form->location = $location;
		$module->rankings = array();
		$employees = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'employee'), 'class' => com_hrm_employee));

		foreach ($employees as $key => $value) {
			if (!isset($value->user_account) || !$value->user_account->in_group($location))
				unset($employees[$key]);
		}

		// Date setup for different weekly and monthly breakdowns.
		$days_passed = format_date(time(), 'custom', 'j');
		$day_in_month = format_date(time(), 'custom', 't');
		if (format_date(time(), 'custom', 'w') == '1') {
			$current_start = strtotime('00:00', time());
		} else {
			$current_start = strtotime('00:00', strtotime('last Monday'));
		}
		if (format_date(time(), 'custom', 'w') == '0') {
			$current_end = strtotime('23:59', time());
		} else {
			$current_end = strtotime('23:59', strtotime('next Sunday'));
		}
		$last_start = strtotime('-1 week', $current_start);
		$last_end = strtotime('-1 week', $current_end);
		
		// Calculate the rankings for all of the employees.
		foreach($employees as $cur_employee) {
			$module->rankings[$cur_employee->guid]['employee'] = $cur_employee;
			$module->rankings[$cur_employee->guid]['current'] = 0;
			$module->rankings[$cur_employee->guid]['last'] = 0;
			$module->rankings[$cur_employee->guid]['mtd'] = 0;
			$module->rankings[$cur_employee->guid]['trend'] = 0;
			$module->rankings[$cur_employee->guid]['pct'] = 0;
			$module->rankings[$cur_employee->guid]['goal'] = $this->goals[$cur_employee->guid];

			// Get the employee's sales totals for the current week.
			$current_week_sales = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'sale'), 'ref' => array('employee' => $cur_employee), 'gte' => array('p_cdate' => $current_start), 'lte' => array('p_cdate' => $curent_end), 'class' => com_sales_sale));
			foreach ($current_week_sales as $cur_week_sale)
				$module->rankings[$cur_employee->guid]['current'] += $cur_week_sale->total;

			// Get the employee's sales totals for this sales period.
			$last_week_sales = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'sale'), 'ref' => array('employee' => $cur_employee), 'gte' => array('p_cdate' => $last_start), 'lte' => array('p_cdate' => $last_end), 'class' => com_sales_sale));
			foreach ($last_week_sales as $last_week_sale)
				$module->rankings[$cur_employee->guid]['last'] += $last_week_sale->total;

			// Get the employee's sales totals for the entire sales period.
			$mtd_sales = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'sale'), 'ref' => array('employee' => $cur_employee), 'gte' => array('p_cdate' => $this->start_date), 'lte' => array('p_cdate' => $this->end_date), 'class' => com_sales_sale));
			foreach ($mtd_sales as $cur_mtd_sale)
				$module->rankings[$cur_employee->guid]['mtd'] += $cur_mtd_sale->total;
			
			$module->rankings[$cur_employee->guid]['trend'] = ($module->rankings[$cur_employee->guid]['mtd']/$days_passed)*$days_in_month;

			// Account for employees potentially having $0 as a goal.
			if ($module->rankings[$cur_employee->guid]['goal'] > 0) {
				$module->rankings[$cur_employee->guid]['pct'] = $module->rankings[$cur_employee->guid]['trend'] / $module->rankings[$cur_employee->guid]['goal'];
			} else {
				$module->rankings[$cur_employee->guid]['pct'] = 100;
			}
		}
		// Sort and rank the employees by their trend percentage.
		usort($module->rankings, array($this, 'sort_ranks'));
		$rank = 1;
		foreach ($module->rankings as &$cur_rank) {
			$cur_rank['rank'] = $rank;
			$rank++;
		}
		unset($cur_rank);
		
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