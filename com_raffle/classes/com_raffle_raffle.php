<?php
/**
 * com_raffle_raffle class.
 *
 * @package Pines
 * @subpackage com_raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A raffle.
 *
 * @package Pines
 * @subpackage com_raffle
 */
class com_raffle_raffle extends entity {
	/**
	 * Load a raffle.
	 * @param int $id The ID of the raffle to load, 0 for a new raffle.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_raffle', 'raffle');
		// Defaults.
		$this->public = true;
		$this->contestants = array();
		$this->public_contestants = array();
		$this->winners = array();
		$this->places = 1;
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
	 * @return com_raffle_raffle The new instance.
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
	 * Complete the raffle by selecting winners.
	 * @return bool True on success, false on failure.
	 */
	public function complete() {
		if ($this->complete)
			return true;
		if ($this->public)
			$contestants = array_merge($this->contestants, $this->public_contestants);
		else
			$contestants = $this->contestants;
		$this->winners = array();
		for ($i = 1; $i <= $this->places; $i++) {
			if (!$contestants)
				break;
			$cur_winner = array_rand($contestants);
			$this->winners[$i] = $contestants[$cur_winner];
			unset($contestants[$cur_winner]);
		}
		$this->complete = true;
		return $this->save();
	}

	/**
	 * Delete the raffle.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted raffle $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the raffle.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		global $pines;
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_raffle_raffle');
		return parent::save();
	}

	/**
	 * Print a complete raffle.
	 * @return module The module.
	 */
	public function print_complete() {
		if (!$this->complete)
			return null;
		global $pines;
		$module = new module('com_raffle', 'raffle/complete', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form to edit the raffle.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_raffle', 'raffle/form', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a public raffle form.
	 * @return module The module.
	 */
	public function print_public() {
		if (!$this->public || $this->complete)
			return null;
		global $pines;
		$module = new module('com_raffle', 'enter', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>