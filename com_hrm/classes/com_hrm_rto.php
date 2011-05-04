<?php
/**
 * com_hrm_rto class.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A request for time off from work.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_rto extends entity {
	/**
	 * Load a rto.
	 * @param int $id The ID of the rto to load, 0 for a new rto.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'rto');
		// Defaults.
		$this->status = 'pending';
		$this->all_day = true;
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
	 * @return com_hrm_rto The new instance.
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
	 * Check if the request is during a time when the employee is scheduled.
	 *
	 * @return bool True or false.
	 */
	public function conflicting() {
		global $pines;

		if ($pines->config->com_hrm->com_calendar) {
			$selector = array('&', 'ref' => array('employee', $this->employee), 'tag' => array('com_calendar', 'event'));
			$selector1 = array('&', 'gte' => array('start', $this->start), 'lte' => array('start', $this->end));
			$selector2 = array('&', 'gte' => array('end', $this->start), 'lte' => array('end', $this->end));
			$selector3 = array('&', 'gte' => array('end', $this->end), 'lte' => array('start', $this->start));

			$conflicts = array_merge(
				$pines->entity_manager->get_entities(array('limit' => 1, 'class' => com_calendar_event), $selector, $selector1),
				$pines->entity_manager->get_entities(array('limit' => 1, 'class' => com_calendar_event), $selector, $selector2),
				$pines->entity_manager->get_entities(array('limit' => 1, 'class' => com_calendar_event), $selector, $selector3)
			);
		} else {
			$conflicts = array();
		}
		return (!empty($conflicts));
	}

	/**
	 * Delete the rto.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted rto $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the rto.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->start))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the rto.
	 */
	public function print_form() {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_hrm', 'timeoff/request', 'content');
		$module->entity = $this;
		$module->requests = array();
		// Load all pending time off requests so they can be edited if needed.
		pines_session();
		$module->requests = $pines->entity_manager->get_entities(array('class' => com_hrm_rto), array('!&', 'data' => array('status', 'approved')), array('&', 'tag' => array('com_hrm', 'rto'), 'ref' => array('user', $_SESSION['user'])));
		$pines->page->override_doc($module->render());
	}
}

?>