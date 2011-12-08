<?php
/**
 * com_calendar_event class.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An event for the company calendar.
 *
 * @package Pines
 * @subpackage com_calendar
 */
class com_calendar_event extends entity {
	/**
	 * Load an event.
	 * @param int $id The ID of the event to load, 0 for a new event.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_calendar', 'event');
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
		$this->private = false;
	}

	/**
	 * Create a new instance.
	 * @return com_calendar_event The new instance.
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
	 * @param string $timezone The timezone to edit the event in.
	 */
	public function print_form($location = null, $timezone = null) {
		global $pines;
		$pines->page->override = true;

		if (empty($timezone)) {
			if (isset($this->user->guid)) {
				$timezone = $this->user->get_timezone;
			} else {
				$timezone = $_SESSION['user']->get_timezone();
			}
		}

		$module = new module('com_calendar', 'form_event', 'content');
		$module->entity = $this;
		$module->timezone = $timezone;
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