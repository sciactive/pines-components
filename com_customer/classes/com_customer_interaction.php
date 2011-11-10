<?php
/**
 * com_customer_interaction class.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A customer interaction.
 *
 * @package Pines
 * @subpackage com_customer
 */
class com_customer_interaction extends entity {
	/**
	 * Load a customer interaction.
	 * @param int $id The ID of the interction to load, 0 for a new interaction.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_customer', 'interaction');
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
		$this->status = 'open';
		$this->review_comments = array();
	}

	/**
	 * Create a new instance.
	 * @return com_customer_interaction The new instance.
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
	 * Delete the interaction.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted interaction $this->guid.", 'notice');
		return true;
	}
}

?>