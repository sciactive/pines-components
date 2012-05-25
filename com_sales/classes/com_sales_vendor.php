<?php
/**
 * com_sales_vendor class.
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
 * A vendor.
 *
 * @package Components\sales
 */
class com_sales_vendor extends entity {
	/**
	 * Load a vendor.
	 * @param int $id The ID of the vendor to load, 0 for a new vendor.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'vendor');
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
		$this->address_type = 'us';
	}

	/**
	 * Create a new instance.
	 * @return com_sales_vendor The new instance.
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
	 * Return the entity helper module.
	 * @return module Entity helper module.
	 */
	public function helper() {
		return new module('com_sales', 'vendor/helper');
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'vendor';
			case 'types':
				return 'vendors';
			case 'url_edit':
				if (gatekeeper('com_sales/editvendor'))
					return pines_url('com_sales', 'vendor/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listvendors'))
					return pines_url('com_sales', 'vendor/list');
				break;
			case 'icon':
				return 'picon-repository';
		}
		return null;
	}

	/**
	 * Delete the vendor.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted vendor $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the vendor.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Get the location of the company logo.
	 * @param bool $full Whether to return a full URL (as opposed to relative).
	 * @return string The location of the company logo.
	 */
	public function get_logo($full = false) {
		global $pines;
		if (isset($this->logo))
			return $full ? $pines->uploader->url($pines->uploader->real($this->logo), true) : $this->logo;
		return '';
	}

	/**
	 * Print a form to edit the vendor.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_sales', 'vendor/form', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>