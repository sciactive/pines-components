<?php
/**
 * com_example_foobar class.
 *
 * @package Components\example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A foobar.
 *
 * @package Components\example
 */
class com_example_foobar extends entity {
	/**
	 * Load a foobar.
	 * @param int $id The ID of the foobar to load, 0 for a new foobar.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_example', 'foobar');
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
		$this->enabled = true;
		$this->attributes = array();
	}

	/**
	 * Create a new instance.
	 * @return com_example_foobar The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'foobar';
			case 'types':
				return 'foobars';
			case 'url_edit':
				if (gatekeeper('com_example/editfoobar'))
					return pines_url('com_example', 'foobar/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_example/listfoobars'))
					return pines_url('com_example', 'foobar/list');
				break;
			case 'icon':
				return 'picon-view-pim-journal';
		}
		return null;
	}

	/**
	 * Delete the foobar.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted foobar $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the foobar.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the foobar.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_example', 'foobar/form', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>