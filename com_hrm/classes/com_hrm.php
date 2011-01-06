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
	 * Whether the employee selector JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded_employee
	 */
	private $js_loaded_employee = false;
	/**
	 * Whether to integrate with com_sales.
	 *
	 * @var bool $com_sales
	 */
	var $com_sales;

	/**
	 * Check whether com_sales is installed and we should integrate with it.
	 *
	 * Places the result in $this->com_sales.
	 */
	public function __construct() {
		global $pines;
		$this->com_sales = $pines->depend->check('component', 'com_sales');
	}

	/**
	 * Get all employees.
	 *
	 * @param bool $employed Get currently employed or past employees.
	 * @return array An array of employees.
	 * @todo Optimize this function.
	 */
	public function get_employees($employed = true) {
		global $pines;
		$users = $pines->user_manager->get_users();
		$employees = array();
		// Filter out users who aren't employees.
		foreach ($users as $key => &$cur_user) {
			if ($cur_user->employee && ($employed xor $cur_user->terminated))
				$employees[] = com_hrm_employee::factory($cur_user->guid);
			unset($users[$key]);
		}
		unset($cur_user);
		return $employees;
	}

	/**
	 * Get all issue types.
	 *
	 * @return array An array of issue types.
	 */
	public function get_issue_types() {
		global $pines;
		return $pines->entity_manager->get_entities(array('class' => com_hrm_issue_type), array('&', 'tag' => array('com_hrm', 'issue_type')));
	}

	/**
	 * Creates and attaches a module which lists employees.
	 * 
	 * @param bool $employed List currently employed or past employees.
	 * @return module The list module.
	 */
	public function list_employees($employed = true) {
		$module = new module('com_hrm', 'employee/list', 'content');
		
		$module->employees = $this->get_employees($employed);
		$module->employed = $employed;

		if ( empty($module->employees) )
			pines_notice('There are no matching employees.');
	}

	/**
	 * Creates and attaches a module which lists issue types.
	 */
	public function list_issue_types() {
		global $pines;

		$module = new module('com_hrm', 'issue/list', 'content');
		$module->types = $pines->entity_manager->get_entities(array('class' => com_hrm_issue_type), array('&', 'tag' => array('com_hrm', 'issue_type')));

		if ( empty($module->types) )
			pines_notice('There are no issue types.');
	}

	/**
	 * Creates and attaches a module which lists employees' timeclocks.
	 */
	public function list_timeclocks() {
		$module = new module('com_hrm', 'employee/timeclock/list', 'content');

		$module->employees = $this->get_employees();

		if ( empty($module->employees) )
			pines_notice('There are no employees.');
	}

	/**
	 * Load the employee selector.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load_employee_select() {
		if (!$this->js_loaded_cust) {
			$module = new module('com_hrm', 'employee/select', 'head');
			$module->render();
			$this->js_loaded_employee = true;
		}
	}

	/**
	 * Creates and attaches a module which lists all pending time off requests.
	 */
	public function review_timeoff() {
		global $pines;
		$pines->page->override = true;
		$module = new module('com_hrm', 'timeoff/review', 'content');
		$module->requests = $pines->entity_manager->get_entities(array('class' => com_hrm_rto), array('&', 'tag' => array('com_hrm', 'rto'), 'data' => array('status', 'pending')));
		$pines->page->override_doc($module->render());
	}

	/**
	 * Sort users.
	 * @param group $a User.
	 * @param group $b User.
	 * @return bool User order.
	 */
	private function sort_users($a, $b) {
		$aname = empty($a->name) ? $a->username : $a->name;
		$bname = empty($b->name) ? $b->username : $b->name;
		return strtolower($aname) > strtolower($bname);
	}

	/**
	 * Print a form to select users.
	 *
	 * @param bool $all Whether to show users who are employees too.
	 * @return module The form's module.
	 */
	public function user_select_form($all = false) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_hrm', 'forms/users');
		$module->users = (array) $pines->user_manager->get_users();
		if (!$all) {
			// Filter out users who are already employees.
			foreach ($module->users as $key => &$cur_user) {
				if ($cur_user->employee)
					unset($module->users[$key]);
			}
			unset($cur_user);
		}
		usort($module->users, array($this, 'sort_users'));

		$pines->page->override_doc($module->render());
		return $module;
	}
}

?>