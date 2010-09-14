<?php
/**
 * com_hrm_issue_type class.
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
 * An employee issue type.
 *
 * @package Pines
 * @subpackage com_hrm
 */
class com_hrm_issue_type extends entity {
	/**
	 * Load an issue type.
	 * @param int $id The ID of the issue to load, 0 for a new issue type.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'issue_type');
		$this->penalty = 0;

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
	 * @return com_hrm_issue_type The new instance.
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
	 * Delete the issue type.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted issue type $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the issue type.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		return parent::save();
	}
}

?>