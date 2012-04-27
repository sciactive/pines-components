<?php
/**
 * com_sales_return_checklist class.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A return checklist.
 *
 * @package Components\sales
 */
class com_sales_return_checklist extends entity {
	/**
	 * Load a return checklist.
	 * @param int $id The ID of the return checklist to load, 0 for a new return checklist.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'return_checklist');
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
	}

	/**
	 * Create a new instance.
	 * @return com_sales_return_checklist The new instance.
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
				return 'return checklist';
			case 'types':
				return 'return checklists';
			case 'url_edit':
				if (gatekeeper('com_sales/editreturnchecklist'))
					return pines_url('com_sales', 'returnchecklist/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listreturnchecklists'))
					return pines_url('com_sales', 'returnchecklist/list');
				break;
			case 'icon':
				return 'picon-view-task';
		}
		return null;
	}

	/**
	 * Delete the return checklist.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted return checklist $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the return checklist.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the return checklist.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'returnchecklist/form', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>