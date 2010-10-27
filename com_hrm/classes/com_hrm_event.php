<?php
/**
 * com_hrm_event class.
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
 * An event for the company calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_event extends entity {
	/**
	 * Load an event.
	 * @param int $id The ID of the event to load, 0 for a new event.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'event');
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
	 * @return com_hrm_event The new instance.
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
	 * Delete the event.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted event $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the event.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->title))
			return false;
		if (!isset($this->event_id))
			$this->event_id = 0;
		return parent::save();
	}

	/**
	 * Print a form to edit the event.
	 * @param group $location The location to create the event for.
	 */
	public function print_form($location = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_hrm', 'form_event', 'content');
		$module->entity = $this;
		// Should work like this, we need to have the employee's group update upon saving it to a user.
		$module->employees = $pines->com_hrm->get_employees();
		$event_location = $this->group->guid;
		if (empty($event_location))
			$event_location = $location->guid;
		$module->location = $event_location;
		$pines->page->override_doc($module->render());
	}
}

?>