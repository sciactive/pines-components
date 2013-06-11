<?php
/**
 * com_content_sidemenu class.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A sidemenu.
 *
 * @package Components\content
 */
class com_content_sidemenu extends entity {
	/**
	 * Load a sidemenu.
	 * @param int $id The ID of the sidemenu to load, 0 for a new sidemenu.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_content', 'sidemenu');
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
	}

	/**
	 * Create a new instance.
	 * @return com_content_sidemenu The new instance.
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
	 * Save the sidemenu.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (empty($this->tags))
			return false;
		return parent::save();
	}
}

?>