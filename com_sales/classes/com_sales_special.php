<?php
/**
 * com_sales_special class.
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
 * A special.
 *
 * @package Components\sales
 */
class com_sales_special extends entity {
	/**
	 * Load a special.
	 * @param int $id The ID of the special to load, 0 for a new special.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'special');
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
		$this->per_ticket = 1;
		$this->conditions = array();
		$this->discounts = array();
		$this->requirements = array();
	}

	/**
	 * Create a new instance.
	 * @return com_sales_special The new instance.
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
				return 'special';
			case 'types':
				return 'specials';
			case 'url_edit':
				if (gatekeeper('com_sales/editspecial'))
					return pines_url('com_sales', 'special/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listspecials'))
					return pines_url('com_sales', 'special/list');
				break;
			case 'icon':
				return 'picon-get-hot-new-stuff';
		}
		return null;
	}

	/**
	 * Delete the special.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted special $this->name.", 'notice');
		return true;
	}

	/**
	 * Determine if this special is eligible.
	 *
	 * This funcition checks all conditions and requirements that can be checked
	 * without a sale.
	 *
	 * @return bool True if the special is eligible, false otherwise.
	 */
	public function eligible() {
		if (!$this->enabled)
			return false;
		global $pines;
		// Check that all conditions are met.
		foreach ((array) $this->conditions as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value))
				return false;
		}
		foreach ((array) $this->requirements as $cur_requirement) {
			switch ($cur_requirement['type']) {
				case 'date_lt':
					if (time() >= $cur_requirement['value'])
						return false;
					break;
				case 'date_gt':
					if (time() < $cur_requirement['value'])
						return false;
					break;
			}
		}
		return true;
	}

	/**
	 * Save the special.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->code))
			return false;
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the special.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_sales', 'special/form', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>