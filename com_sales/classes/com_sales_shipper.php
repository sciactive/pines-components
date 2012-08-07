<?php
/**
 * com_sales_shipper class.
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
 * A shipper.
 *
 * @package Components\sales
 */
class com_sales_shipper extends entity {
	/**
	 * Load a shipper.
	 * @param int $id The ID of the shipper to load, 0 for a new shipper.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'shipper');
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
		$this->tracking = 'custom';
	}

	/**
	 * Create a new instance.
	 * @return com_sales_shipper The new instance.
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
		return new module('com_sales', 'shipper/helper');
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'shipper';
			case 'types':
				return 'shippers';
			case 'url_edit':
				if (gatekeeper('com_sales/editshipper'))
					return pines_url('com_sales', 'shipper/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listshippers'))
					return pines_url('com_sales', 'shipper/list');
				break;
			case 'icon':
				return 'picon-mail-folder-outbox';
		}
		return null;
	}

	/**
	 * Delete the shipper.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted shipper $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the shipper.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the shipper.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_sales', 'shipper/form', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Get the tracking URL.
	 * @param string $number The tracking number.
	 * @return string The tracking URL.
	 */
	public function tracking_url($number) {
		if ($this->tracking == 'custom')
			return str_replace('#tracking_number#', urlencode($number), $this->tracking_url);
		global $pines;
		return str_replace('#tracking_number#', urlencode($number), $pines->com_sales->tracking_urls[$this->tracking]);
	}
}

?>