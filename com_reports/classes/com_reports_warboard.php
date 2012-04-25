<?php
/**
 * com_reports_warboard class.
 *
 * @package Components
 * @subpackage reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A list of employee contact information.
 *
 * @package Components
 * @subpackage reports
 */
class com_reports_warboard extends entity {
	/**
	 * Load a warboard.
	 * @param int $id The ID of the warboard to load, 0 for a new warboard.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_reports', 'warboard');
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
		$this->company_name = 'Company Name';
		$this->columns = 5;
		$this->positions = array();
		$this->locations = array();
		$this->important = array();
		$this->hq = $_SESSION['user']->group;
	}

	/**
	 * Create a new instance.
	 * @return com_reports_warboard The new instance.
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
	 * Delete the warboard.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted warboard [$this->title].", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the warboard.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;

		$form = new module('com_reports', 'form_warboard', 'content');
		$form->entity = $this;
		$form->groups = $pines->com_user->get_groups();
		$employees = $pines->com_hrm->get_employees();
		$form->job_titles = array();
		foreach ($employees as $cur_employee) {
			if ($cur_employee->job_title != '' && !in_array($cur_employee->job_title, $form->job_titles))
				$form->job_titles[] = $cur_employee->job_title;
		}
	}

	/**
	 * Show the company warboard.
	 * @return module The module.
	 */
	public function show() {
		global $pines;

		$head = new module('com_reports', 'warboard_head', 'head');
		$module = new module('com_reports', 'warboard', 'content');
		$module->entity = $this;
		/*
		//$pines->com_user->group_sort($this->locations, 'name');
		foreach ($this->locations as $cur_l) {
			echo "{$cur_l->parent->name} : {$cur_l->name}<br />";
		}
		echo "<br /><br /><br />";
		*/
		$pines->entity_manager->psort($this->locations, 'name', 'parent');
		/*
		foreach ($this->locations as $cur_l) {
			echo "{$cur_l->parent->name} : {$cur_l->name}<br />";
		}
		*/
		$pines->entity_manager->sort($this->important, 'name');

		return $module;
	}
}

?>