<?php
/**
 * com_reports_paystub class.
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
 * A list of employee payroll for a given pay period.
 *
 * @package Pines
 * @subpackage com_reports
 */
class com_reports_paystub extends entity {
	/**
	 * Load a paystub.
	 * @param int $id The ID of the paystub to load, 0 for a new paystub.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_reports', 'paystub');
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
		$this->payroll = array();
	}

	/**
	 * Create a new instance.
	 * @return com_reports_paystub The new instance.
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

		$module = new module('com_reports', 'form_paystub', 'content');
		$module->entity = $this;
		$module->employees = $pines->com_hrm->get_employees();

		return $module;
	}

	/**
	 * Creates and attaches a module which reports payroll.
	 *
	 * @param bool $entire_company Whether or not to show the entire company
	 * @param group $location The group to report on.
	 * @param bool $descendants Whether to show descendant locations.
	 * @return module The paystub report module.
	 */
	function show($entire_company = true, $location = null, $descendants = false) {
		global $pines;

		$module = new module('com_reports', 'report_paystub', 'content');
		$module->entity = $this;
		$module->entire_company = $entire_company;
		$module->location = $location;
		$module->descendants = $descendants;

		return $module;
	}

	/**
	 * Sort by the total payment amount.
	 *
	 * @param array $a The first entry.
	 * @param array $b The second entry.
	 * @return int The sort order.
	 * @access private
	 */
	private function sort_payroll($a, $b) {
		if ($a['total'] > $b['total'])
			return -1;
		if ($a['total'] < $b['total'])
			return 1;
		return 0;
	}
}

?>