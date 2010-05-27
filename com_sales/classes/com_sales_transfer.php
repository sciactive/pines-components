<?php
/**
 * com_sales_transfer class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A transfer.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_transfer extends entity {
	/**
	 * Load a transfer.
	 * @param int $id The ID of the transfer to load, 0 for a new transfer.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'transfer');
		// Defaults.
		$this->stock = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 * @return com_sales_transfer The new instance.
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
	 * Delete the transfer.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		// Don't delete the transfer if it has received items.
		if (!empty($this->received))
			return false;
		if (!parent::delete())
			return false;
		pines_log("Deleted transfer $this->transfer_number.", 'notice');
		return true;
	}

	/**
	 * Save the transfer.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!is_array($this->stock))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the transfer.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'form_transfer', 'content');
		$module->entity = $this;
		$module->locations = $pines->user_manager->get_group_array();
		$module->shippers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'shipper'), 'class' => com_sales_shipper));
		if (!is_array($module->shippers))
			$module->shippers = array();
		$module->stock = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
		if (!is_array($module->stock))
			$module->stock = array();

		return $module;
	}
}

?>