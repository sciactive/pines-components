<?php
/**
 * com_hrm_timeclock_entry class.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A timeclock entry. The employee should be the user of this entity.
 *
 * @package Components\hrm
 * @property int $in In time.
 * @property int $out Out time.
 * @property string $comment An optional comment.
 * @property array $extras Any extra information.
 */
class com_hrm_timeclock_entry extends entity {
	/**
	 * Load a timeclock.
	 * @param int $id The ID of the timeclock to load, 0 for a new timeclock.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'timeclock_entry');
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
		$this->extras = array();
		$this->ac = (object) array('user' => 3, 'group' => 3, 'other' => 2);
	}

	/**
	 * Create a new instance.
	 * @return com_hrm_timeclock_entry The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}
}

?>